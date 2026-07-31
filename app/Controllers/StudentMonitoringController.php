<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Permissions;
use App\Core\Authorization;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\Monitoring\StudentMonitoringService;
use App\Services\Monitoring\StudentMonitoringActionAttachmentService;
use Throwable;

final class StudentMonitoringController extends BaseController
{
    public function __construct(private StudentMonitoringService $service, private StudentMonitoringActionAttachmentService $attachmentService) {}

    public function index(): void
    {
        if (!$this->guard() || !$this->authorize(Permissions::STUDENT_MONITORING_VIEW)) return;
        $user=(array)Session::get('user',[]);
        $all=Authorization::hasAnyRole(['ADMIN','DIRECTION','COORDINATION']) && Request::get('visao','meus')==='todos';
        $dashboard=$this->service->dashboardPage((int)($user['id']??0),$all,[
            'class_id'=>Request::get('turma',0),
            'status'=>Request::get('status',''),
            'reason'=>Request::get('motivo',''),
            'teacher_id'=>Request::get('professor',0),
            'start_date'=>Request::get('inicio',''),
            'end_date'=>Request::get('fim',''),
            'risk'=>Request::get('risco',''),
            'order'=>Request::get('ordem','end_desc'),
            'page'=>Request::get('pagina',1),
            'per_page'=>Request::get('por_pagina',10),
        ]);
        $this->view('pages/monitoring/index',[
            'title'=>'Alunos acompanhados - '.app_name(),
            'items'=>$dashboard['items'],
            'filters'=>$dashboard['filters'],
            'pagination'=>$dashboard['pagination'],
            'summary'=>$dashboard['summary'],
            'filterOptions'=>$dashboard['options'],
            'allSchool'=>$all,
            'canViewAll'=>Authorization::hasAnyRole(['ADMIN','DIRECTION','COORDINATION']),
            'currentUserId'=>(int)($user['id']??0),
            'isManagement'=>Authorization::hasAnyRole(['ADMIN','DIRECTION','COORDINATION']),
            'actionTypeOptions'=>$this->service->actionTypeOptions(),
            'issueTypeOptions'=>$this->service->issueTypeOptions(),
            'success'=>Session::get('monitoring_success'),
            'error'=>Session::get('monitoring_error'),
        ]);
        Session::remove('monitoring_success'); Session::remove('monitoring_error');
    }

    public function store(): void
    {
        if (!$this->guard() || !$this->authorize(Permissions::STUDENT_MONITORING_MANAGE)) return;
        $createdMonitoringId=0;
        try{$createdMonitoringId=$this->service->start($_POST,(array)Session::get('user',[]));Session::set('monitoring_success','Acompanhamento e plano salvos com sucesso.');}
        catch(Throwable $e){Session::set('monitoring_error',$e->getMessage());}
        $studentId=(int)Request::post('student_id',0);
        Response::redirect($this->profileRedirect($studentId,$createdMonitoringId));
    }


    public function addParticipants(): void
    {
        if (!$this->guard() || !$this->authorize(Permissions::STUDENT_MONITORING_MANAGE)) return;
        try{$this->service->addParticipants($_POST,(array)Session::get('user',[]));Session::set('monitoring_success','Novo acompanhante adicionado com sucesso.');}
        catch(Throwable $e){Session::set('monitoring_error',$e->getMessage());}
        $studentId=(int)Request::post('student_id',0);
        Response::redirect($this->profileRedirect($studentId,(int)Request::post('monitoring_id',0)));
    }

    public function savePlan(): void
    {
        if (!$this->guard() || !$this->authorize(Permissions::STUDENT_MONITORING_MANAGE)) return;
        try{$this->service->savePlan($_POST,(array)Session::get('user',[]));Session::set('monitoring_success','Plano de acompanhamento salvo com sucesso.');}
        catch(Throwable $e){Session::set('monitoring_error',$e->getMessage());}
        $studentId=(int)Request::post('student_id',0);
        Response::redirect($this->profileRedirect($studentId,(int)Request::post('monitoring_id',0)));
    }

    public function stop(): void
    {
        if (!$this->guard() || !$this->authorize(Permissions::STUDENT_MONITORING_MANAGE)) return;
        $targetUserId=(int)Request::post('user_id',0);
        $this->service->stop((int)Request::post('monitoring_id',0),(array)Session::get('user',[]),$targetUserId);
        Session::set('monitoring_success',$targetUserId>0?'Pessoa removida do acompanhamento.':'Você deixou de acompanhar este aluno.');
        $studentId=(int)Request::post('student_id',0);
        Response::redirect($this->profileRedirect($studentId,(int)Request::post('monitoring_id',0)));
    }


    public function extend(): void
    {
        if (!$this->guard() || !$this->authorize(Permissions::STUDENT_MONITORING_MANAGE)) return;
        try{$this->service->extend($_POST,(array)Session::get('user',[]));Session::set('monitoring_success','Prazo do acompanhamento prorrogado com histórico preservado.');}
        catch(Throwable $e){Session::set('monitoring_error',$e->getMessage());}
        $studentId=(int)Request::post('student_id',0);
        Response::redirect($this->profileRedirect($studentId,(int)Request::post('monitoring_id',0)));
    }


    public function reopenAbsenceTreatment(): void
    {
        if (!$this->guard() || !$this->authorize(Permissions::STUDENT_MONITORING_MANAGE)) return;
        try{$this->service->reopenAbsenceTreatment($_POST,(array)Session::get('user',[]));Session::set('monitoring_success','Tratamento das faltas reaberto com histórico preservado.');}
        catch(Throwable $e){Session::set('monitoring_error',$e->getMessage());}
        $studentId=(int)Request::post('student_id',0);
        Response::redirect($this->profileRedirect($studentId,(int)Request::post('monitoring_id',0),'studentIntelligence'));
    }

    public function storeAction(): void
    {
        if (!$this->guard() || !$this->authorize(Permissions::STUDENT_MONITORING_MANAGE)) return;
        try{$user=(array)Session::get('user',[]);$actionId=$this->service->createAction($_POST,$user);if(isset($_FILES['attachments'])){$this->service->uploadActionAttachments($actionId,$_FILES['attachments'],$user);}Session::set('monitoring_success','Ação registrada com sucesso.');}
        catch(Throwable $e){Session::set('monitoring_error',$e->getMessage());}
        $studentId=(int)Request::post('student_id',0);
        Response::redirect($this->profileRedirect($studentId,(int)Request::post('monitoring_id',0),'studentIntelligence'));
    }

    public function updateAction(): void
    {
        if (!$this->guard() || !$this->authorize(Permissions::STUDENT_MONITORING_MANAGE)) return;
        try{$user=(array)Session::get('user',[]);$this->service->updateAction($_POST,$user);if(isset($_FILES['attachments'])){$this->service->uploadActionAttachments((int)Request::post('action_id',0),$_FILES['attachments'],$user);}Session::set('monitoring_success','Ação atualizada com sucesso.');}
        catch(Throwable $e){Session::set('monitoring_error',$e->getMessage());}
        $studentId=(int)Request::post('student_id',0);
        Response::redirect($this->profileRedirect($studentId,(int)Request::post('monitoring_id',0),'studentIntelligence'));
    }

    public function deleteAction(): void
    {
        if (!$this->guard() || !$this->authorize(Permissions::STUDENT_MONITORING_MANAGE)) return;
        try{$this->service->deleteAction((int)Request::post('action_id',0),(array)Session::get('user',[]));Session::set('monitoring_success','Ação excluída do histórico.');}
        catch(Throwable $e){Session::set('monitoring_error',$e->getMessage());}
        $studentId=(int)Request::post('student_id',0);
        Response::redirect($this->profileRedirect($studentId,(int)Request::post('monitoring_id',0),'studentIntelligence'));
    }

    public function deleteActionAttachment(): void
    {
        if (!$this->guard() || !$this->authorize(Permissions::STUDENT_MONITORING_MANAGE)) return;
        try{$this->service->removeActionAttachment((int)Request::post('attachment_id',0),(array)Session::get('user',[]));Session::set('monitoring_success','Anexo removido com sucesso.');}
        catch(Throwable $e){Session::set('monitoring_error',$e->getMessage());}
        $monitoringId=(int)Request::post('monitoring_id',0);
        Response::redirect(base_url('acompanhamentos').($monitoringId>0?'#historico-'.$monitoringId:''));
    }

    public function updateRecommendation(): void
    {
        if (!$this->guard() || !$this->authorize(Permissions::STUDENT_MONITORING_MANAGE)) return;
        try{$this->service->updateRecommendation($_POST,(array)Session::get('user',[]));Session::set('monitoring_success','Recomendação atualizada com sucesso.');}
        catch(Throwable $e){Session::set('monitoring_error',$e->getMessage());}
        Response::redirect(base_url('acompanhamentos'));
    }

    public function deleteConcludedLink(): void
    {
        if (!$this->guard() || !$this->authorize(Permissions::STUDENT_MONITORING_MANAGE)) return;
        try {
            $this->service->deleteConcludedLink((int)Request::post('monitoring_user_id',0),(array)Session::get('user',[]));
            Session::set('monitoring_success','Vínculo concluído excluído com sucesso.');
        } catch (Throwable $e) {
            Session::set('monitoring_error',$e->getMessage());
        }
        Response::redirect(base_url('acompanhamentos'));
    }


    private function profileRedirect(int $studentId, int $monitoringId=0, string $anchor='studentMonitoring'): string
    {
        if($studentId<=0) return base_url('acompanhamentos');
        $url=base_url('alunos/perfil').'?id='.$studentId;
        if($monitoringId>0) $url.='&case_id='.$monitoringId;
        return $url.'#'.$anchor;
    }

}
