<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Permissions;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\SchoolYearService;
use App\Services\SchoolPeriodService;
use App\Services\SchoolDayService;
use App\Services\AcademicLifecycleService;
use Throwable;

final class SchoolYearController extends BaseController
{
    public function __construct(private SchoolYearService $service, private SchoolPeriodService $periods, private SchoolDayService $days,private AcademicLifecycleService $lifecycle)
    {
    }

    public function index(): void
    {
        if (!$this->guard() || !$this->authorize(Permissions::SETTINGS_MANAGE)) {
            return;
        }

        $editingId = max(0, (int) Request::get('editar', 0));
        $overview=$this->service->overview($editingId ?: null);
        $selectedYear=max(0,(int)Request::get('ano',0)) ?: (int)($overview['activeYear']['id'] ?? ($overview['years'][0]['id'] ?? 0));
        $this->view('pages/settings/school-years/index', array_merge(
            ['title' => 'Ano Letivo', 'csrfToken' => Csrf::token()],
            $overview,
            ['selectedYearId'=>$selectedYear,'periods'=>$selectedYear?$this->periods->forYear($selectedYear):[],'schoolDays'=>$selectedYear?$this->days->data($selectedYear):['days'=>[],'summary'=>[]],'academicHistory'=>$this->lifecycle->history(),'academicAudits'=>$this->lifecycle->audits()],
            ['success' => flash('school_year_success'), 'error' => flash('school_year_error')]
        ));
    }

    public function store(): void
    {
        $this->execute(
            fn () => $this->service->create($_POST),
            'Ano letivo criado com sucesso.'
        );
    }

    public function update(): void
    {
        $id = max(0, (int) Request::post('id', 0));
        $this->execute(
            fn () => $this->service->update($id, $_POST),
            'Ano letivo atualizado com sucesso.'
        );
    }

    public function activate(): void
    {
        $this->execute(
            fn () => $this->service->activate((int) Request::post('id', 0)),
            'Ano letivo definido como ativo.'
        );
    }

    public function close(): void
    {
        $this->closeYearSafely();
    }

    public function archive(): void
    {
        $this->execute(
            fn () => $this->service->archive((int) Request::post('id', 0)),
            'Ano letivo arquivado.'
        );
    }

    public function reopen(): void
    {
        $this->execute(
            fn () => $this->service->reopen((int) Request::post('id', 0)),
            'Ano letivo reaberto em preparação.'
        );
    }

    public function savePeriod(): void
    {
        $id=max(0,(int)Request::post('id',0));
        $this->execute(fn()=>$this->periods->save($id?:null,$_POST),'Período letivo salvo com sucesso.');
    }

    public function deletePeriod(): void
    {
        $this->execute(fn()=>$this->periods->delete((int)Request::post('id',0)),'Período letivo excluído.');
    }

    public function generateDays(): void
    {
        $yearId=(int)Request::post('school_year_id',0);
        $this->execute(fn()=>$this->days->generateWeekdays($yearId),'Calendário-base de dias letivos gerado.');
    }

    public function saveDay(): void
    {
        $this->execute(fn()=>$this->days->save($_POST),'Dia escolar atualizado.');
    }
    public function closePeriod(): void{$this->execute(fn()=>$this->lifecycle->closePeriod((int)Request::post('id',0),$this->userId(),$_SERVER['REMOTE_ADDR']??null),'Período fechado e snapshot criado.');}
    public function reopenPeriod(): void{$this->execute(fn()=>$this->lifecycle->reopenPeriod((int)Request::post('id',0),$this->userId(),$_SERVER['REMOTE_ADDR']??null),'Período reaberto.');}
    public function closeYearSafely(): void{$this->execute(fn()=>$this->lifecycle->closeYear((int)Request::post('id',0),$this->userId(),$_SERVER['REMOTE_ADDR']??null),'Ano encerrado e snapshot final criado.');}
    public function createNextYear(): void{$this->execute(fn()=>$this->lifecycle->createNextYear((int)Request::post('source_year_id',0),$_POST,$this->userId(),$_SERVER['REMOTE_ADDR']??null),'Novo ano preparado pelo assistente.');}
    private function userId(): int{return (int)(Session::get('user')['id']??0);}

    private function execute(callable $operation, string $success): void
    {
        if (!$this->guard() || !$this->authorize(Permissions::SETTINGS_MANAGE)) {
            return;
        }
        if (!Csrf::validate((string) Request::post('_token', ''))) {
            Session::set('school_year_error', 'A sessão do formulário expirou. Tente novamente.');
            Response::redirect(base_url('configuracoes/ano-letivo'));
        }

        try {
            $operation();
            Session::set('school_year_success', $success);
        } catch (Throwable $exception) {
            Session::set('school_year_error', $exception->getMessage());
        }

        Response::redirect(base_url('configuracoes/ano-letivo'));
    }
}
