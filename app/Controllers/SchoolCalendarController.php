<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Permissions;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Csrf;
use App\Services\SchoolCalendarService;
use App\Services\SchoolYearService;
use App\Services\SchoolPeriodService;
use InvalidArgumentException;
use Throwable;

class SchoolCalendarController extends BaseController
{
    public function __construct(private SchoolCalendarService $service,private SchoolYearService $years,private SchoolPeriodService $periods){}

    public function index(): void
    {
        if(!$this->guard()||!$this->authorize(Permissions::CALENDAR_VIEW)) return;
        $this->view('pages/calendar/index',[
            'title'=>'Calendário Escolar - '.app_name(),
            'events'=>$this->service->all(),
            'types'=>$this->service->types(),
            'calendarSuccess'=>Session::get('calendar_success'),
            'calendarError'=>Session::get('calendar_error'),
        ]);
        Session::remove('calendar_success'); Session::remove('calendar_error');
    }

    public function create(): void
    {
        if(!$this->guard()||!$this->authorize(Permissions::CALENDAR_MANAGE,base_url('calendario'))) return;
        $this->view('pages/calendar/form',array_merge(['title'=>'Novo evento - '.app_name(),'types'=>$this->service->types(),'event'=>[],'calendarError'=>Session::get('calendar_error')],$this->formContext()));
        Session::remove('calendar_error');
    }

    public function edit(): void
    {
        if(!$this->guard()||!$this->authorize(Permissions::CALENDAR_MANAGE,base_url('calendario'))) return;
        $event=$this->service->find((int)Request::get('id'));
        if(!$event){ Session::set('calendar_error','Evento não encontrado.'); Response::redirect(base_url('calendario')); return; }
        $this->view('pages/calendar/form',array_merge(['title'=>'Editar evento - '.app_name(),'types'=>$this->service->types(),'event'=>$event,'calendarError'=>Session::get('calendar_error')],$this->formContext()));
        Session::remove('calendar_error');
    }

    public function store(): void { $this->save(true); }
    public function update(): void { $this->save(false); }

    private function save(bool $creating): void
    {
        if(!$this->guard()||!$this->authorize(Permissions::CALENDAR_MANAGE,base_url('calendario'))) return;
        if(!Csrf::validate((string)Request::post('_token',''))){Session::set('calendar_error','A sessão do formulário expirou.');Response::redirect(base_url('calendario'));return;}
        try {
            $data=Request::all();
            if($creating){ $data['created_by']=(int)(Session::get('user')['id']??0); $this->service->create($data); }
            else { $this->service->update((int)Request::post('id'),$data); }
            Session::set('calendar_success',$creating?'Evento cadastrado com sucesso.':'Evento atualizado com sucesso.');
            Response::redirect(base_url('calendario'));
        } catch(InvalidArgumentException $e){ Session::set('calendar_error',$e->getMessage()); Response::redirect(base_url($creating?'calendario/novo':'calendario/editar?id='.(int)Request::post('id'))); }
        catch(Throwable $e){ Session::set('calendar_error','Não foi possível salvar o evento.'); Response::redirect(base_url('calendario')); }
    }

    public function delete(): void
    {
        if(!$this->guard()||!$this->authorize(Permissions::CALENDAR_MANAGE,base_url('calendario'))) return;
        $this->service->delete((int)Request::post('id'));
        Session::set('calendar_success','Evento excluído.');
        Response::redirect(base_url('calendario'));
    }
    private function formContext(): array
    {
        $overview=$this->years->overview();$yearId=(int)($overview['activeYear']['id']??0);
        return ['schoolYears'=>$overview['years'],'schoolPeriods'=>$yearId?$this->periods->forYear($yearId):[],'csrfToken'=>Csrf::token()];
    }
}
