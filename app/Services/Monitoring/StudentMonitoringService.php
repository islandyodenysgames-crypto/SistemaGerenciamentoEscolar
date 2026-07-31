<?php

declare(strict_types=1);

namespace App\Services\Monitoring;

use App\Auth\Roles;
use App\Core\Authorization;
use App\Repositories\Monitoring\StudentMonitoringRepository;
use App\Services\Intelligence\IntelligenceService;
use App\Services\Occurrence\NotificationService;
use InvalidArgumentException;

final class StudentMonitoringService
{
    private const OBJECTIVE_OPTIONS = [
        'IMPROVE_ATTENDANCE' => 'Melhorar a frequência',
        'REDUCE_OCCURRENCES' => 'Reduzir ocorrências',
        'IMPROVE_LEARNING' => 'Melhorar aprendizagem ou rendimento',
        'INCREASE_ENGAGEMENT' => 'Aumentar engajamento e participação',
        'STRENGTHEN_FAMILY' => 'Fortalecer articulação com a família',
        'SOCIOEMOTIONAL_SUPPORT' => 'Promover apoio socioemocional',
        'MAINTAIN_EVOLUTION' => 'Manter evolução recente',
        'OTHER' => 'Outro objetivo',
    ];

    private const STRATEGY_OPTIONS = [
        'STUDENT_CONVERSATION' => 'Conversar individualmente com o aluno',
        'FAMILY_CONTACT' => 'Conversar com responsáveis',
        'WEEKLY_FOLLOWUP' => 'Realizar acompanhamento semanal',
        'LEARNING_SUPPORT' => 'Articular reforço ou apoio pedagógico',
        'COORDINATION_REFERRAL' => 'Encaminhar para coordenação',
        'SOCIOEMOTIONAL_SUPPORT' => 'Articular apoio socioemocional',
        'ATTENDANCE_GOAL' => 'Estabelecer meta de frequência',
        'POSITIVE_FEEDBACK' => 'Realizar devolutivas positivas',
        'OTHER' => 'Outra estratégia',
    ];

    private const ACTION_TYPE_OPTIONS = [
        'STUDENT_CONVERSATION' => 'Conversa com o aluno',
        'FAMILY_CONVERSATION' => 'Conversa com responsáveis',
        'INDIVIDUAL_SERVICE' => 'Atendimento individual',
        'COORDINATION_REFERRAL' => 'Encaminhamento à coordenação',
        'SPECIALIZED_REFERRAL' => 'Encaminhamento ao serviço especializado',
        'MEETING' => 'Reunião',
        'PHONE_CONTACT' => 'Contato telefônico',
        'FAMILY_MESSAGE' => 'Mensagem aos responsáveis',
        'HOME_VISIT' => 'Visita domiciliar',
        'OBSERVATION' => 'Observação',
        'OTHER' => 'Outra ação',
    ];

    private const RESULT_OPTIONS = [
        'IMPROVED' => 'Melhorou',
        'UNCHANGED' => 'Sem alteração',
        'WORSENED' => 'Piorou',
        'OBSERVATION' => 'Em observação',
    ];

    private const ISSUE_TYPE_OPTIONS = [
        'GENERAL' => 'Acompanhamento geral',
        'UNJUSTIFIED_ABSENCES' => 'Faltas sem justificativa',
        'OCCURRENCE' => 'Ocorrência',
    ];

    private const TREATMENT_STATUS_OPTIONS = [
        'IDENTIFIED' => 'Problema identificado',
        'INVESTIGATING' => 'Em investigação',
        'CAUSE_IDENTIFIED' => 'Motivo identificado',
        'ACTION_TAKEN' => 'Ação realizada',
        'OBSERVING' => 'Em observação',
        'IMPROVED' => 'Melhorou',
        'RESOLVED' => 'Resolvido',
        'NO_IMPROVEMENT' => 'Sem melhora',
    ];

    private const REASON_OPTIONS = [
        'FREQUENCY' => 'Baixa frequência ou faltas recorrentes',
        'OCCURRENCES' => 'Ocorrências disciplinares ou comportamentais',
        'LEARNING' => 'Dificuldades de aprendizagem ou rendimento',
        'ENGAGEMENT' => 'Baixo engajamento ou participação',
        'SOCIOEMOTIONAL' => 'Questões socioemocionais ou de convivência',
        'EVOLUTION' => 'Acompanhar melhora ou evolução recente',
        'FAMILY' => 'Necessidade de articulação com a família',
        'OTHER' => 'Outro motivo',
    ];

    public function __construct(
        private StudentMonitoringRepository $repository,
        private IntelligenceService $intelligenceService,
        private NotificationService $notificationService,
        private StudentMonitoringActionAttachmentService $attachmentService
    ) {}

    public function actionTypeOptions(): array { return self::ACTION_TYPE_OPTIONS; }
    public function issueTypeOptions(): array { return self::ISSUE_TYPE_OPTIONS; }

    public function dashboard(int $userId, bool $allSchool=false): array
    {
        return $this->dashboardPage($userId,$allSchool,[])['items'];
    }

    public function dashboardPage(int $userId,bool $allSchool,array $filters): array
    {
        $this->concludeExpiredLinks($userId);
        $canViewAll=$allSchool && Roles::hasFullAccess((string)Authorization::role());
        $items=$this->repository->allForUser($userId,$canViewAll);

        foreach($items as &$item){
            $item['users']=$this->repository->users((int)$item['id']);
            $item['metrics']=$this->repository->periodMetrics((int)$item['student_id'],(string)$item['start_date'],min((string)$item['end_date'],date('Y-m-d')));
            $item['suggestions']=$this->suggestions((int)$item['student_id'],$item['metrics']);
            $link=$this->repository->activeUserLink((int)$item['id'],$userId);
            $historicalLink=$this->repository->userLink((int)$item['id'],$userId);
            $item['current_user_link']=$link??$historicalLink;
            $item['plan']=$this->repository->activePlanForMonitoring((int)$item['id']);
            $item['is_concluded']=(string)($item['status']??'')!=='ACTIVE' || ((string)($item['end_date']??'')!=='' && (string)$item['end_date']<date('Y-m-d'));
            $item['can_register_action']=$link!==null&&!$item['is_concluded'];
            $item['can_delete_concluded']=Roles::hasFullAccess((string)Authorization::role())&&$item['is_concluded']&&$historicalLink!==null;
            $item['monitoring_user_id']=(int)($historicalLink['id']??0);
            $item['actions']=$this->repository->actionsForMonitoring((int)$item['id']);
            $attachments=$this->attachmentService->groupedByActions(array_column($item['actions'],'id'));
            foreach($item['actions'] as &$actionWithFiles){$actionWithFiles['attachments']=$attachments[(int)$actionWithFiles['id']]??[];}
            unset($actionWithFiles);
            $canManageAllActions=Roles::hasFullAccess((string)Authorization::role());
            foreach($item['actions'] as &$monitoringAction){$monitoringAction['can_manage']=empty($item['is_concluded'])&&($canManageAllActions||(int)($monitoringAction['created_by']??0)===$userId);}
            unset($monitoringAction);
            $item['extensions']=$this->repository->extensionsForMonitoring((int)$item['id']);
            $item['metrics']['intervention_comparison']=$this->interventionComparison((int)$item['student_id'],(string)$item['start_date'],min((string)$item['end_date'],date('Y-m-d')),$item['actions']);
            $item['monitoring_insights']=$this->intelligenceService->monitoringInsights($item,$item['plan'],$item['metrics'],$item['actions']);
            $item['recommendation']=$this->repository->currentRecommendation((int)$item['id']);
            $latestActionDate=(string)($item['actions'][0]['action_date']??$item['start_date']??date('Y-m-d'));
            $item['days_without_action']=max(0,(int)floor((strtotime(date('Y-m-d'))-strtotime($latestActionDate))/86400));
            $item['needs_review']=empty($item['is_concluded'])&&($item['days_without_action']>=7||!empty($item['recommendation']));
            $item['risk_level']=$this->monitoringRiskLevel($item);
            $item['reason_label']=self::REASON_OPTIONS[(string)($item['reason']??'')]??((string)($item['reason']??'')!==''?(string)$item['reason']:'Não informado');
        }
        unset($item);

        $normalized=$this->normalizeDashboardFilters($filters);
        $filtered=array_values(array_filter($items,function(array $item) use($normalized): bool {
            if($normalized['class_id']>0 && (int)($item['class_id']??0)!==$normalized['class_id']) return false;
            if($normalized['status']==='ACTIVE' && !empty($item['is_concluded'])) return false;
            if($normalized['status']==='CONCLUDED' && empty($item['is_concluded'])) return false;
            if($normalized['reason']!=='' && (string)($item['reason']??'')!==$normalized['reason']) return false;
            if($normalized['teacher_id']>0){
                $found=false; foreach((array)$item['users'] as $participant){if((int)($participant['user_id']??0)===$normalized['teacher_id']){$found=true;break;}}
                if(!$found) return false;
            }
            if($normalized['start_date']!=='' && (string)($item['end_date']??'')<$normalized['start_date']) return false;
            if($normalized['end_date']!=='' && (string)($item['start_date']??'')>$normalized['end_date']) return false;
            if($normalized['risk']!=='' && (string)($item['risk_level']??'LOW')!==$normalized['risk']) return false;
            return true;
        }));

        usort($filtered,function(array $a,array $b) use($normalized): int {
            return match($normalized['order']){
                'student_asc'=>strnatcasecmp((string)$a['student_name'],(string)$b['student_name']),
                'end_asc'=>strcmp((string)$a['end_date'],(string)$b['end_date']),
                'risk_desc'=>$this->riskWeight((string)$b['risk_level'])<=>$this->riskWeight((string)$a['risk_level']),
                default=>strcmp((string)$b['end_date'],(string)$a['end_date']) ?: ((int)$b['id']<=>(int)$a['id']),
            };
        });

        $total=count($filtered);
        $pages=max(1,(int)ceil($total/$normalized['per_page']));
        $page=min($normalized['page'],$pages);
        $offset=($page-1)*$normalized['per_page'];
        $pageItems=array_slice($filtered,$offset,$normalized['per_page']);

        $classes=[];$teachers=[];
        foreach($items as $item){
            if((int)($item['class_id']??0)>0)$classes[(int)$item['class_id']]=trim((string)($item['class_year']??'').' '.(string)($item['class_name']??''));
            foreach((array)$item['users'] as $participant)$teachers[(int)$participant['user_id']]=(string)$participant['name'];
        }
        asort($classes,SORT_NATURAL|SORT_FLAG_CASE);asort($teachers,SORT_NATURAL|SORT_FLAG_CASE);

        return [
            'items'=>$pageItems,
            'filters'=>$normalized,
            'pagination'=>['page'=>$page,'pages'=>$pages,'per_page'=>$normalized['per_page'],'total'=>$total],
            'summary'=>[
                'total'=>$total,
                'active'=>count(array_filter($filtered,static fn(array $item): bool=>empty($item['is_concluded']))),
                'concluded'=>count(array_filter($filtered,static fn(array $item): bool=>!empty($item['is_concluded']))),
                'review'=>count(array_filter($filtered,static fn(array $item): bool=>!empty($item['needs_review']))),
            ],
            'options'=>['classes'=>$classes,'teachers'=>$teachers,'reasons'=>self::REASON_OPTIONS],
        ];
    }

    private function normalizeDashboardFilters(array $filters): array
    {
        $status=strtoupper(trim((string)($filters['status']??'')));
        $risk=strtoupper(trim((string)($filters['risk']??'')));
        $order=(string)($filters['order']??'end_desc');
        return [
            'class_id'=>max(0,(int)($filters['class_id']??0)),
            'status'=>in_array($status,['ACTIVE','CONCLUDED'],true)?$status:'',
            'reason'=>array_key_exists((string)($filters['reason']??''),self::REASON_OPTIONS)?(string)$filters['reason']:'',
            'teacher_id'=>max(0,(int)($filters['teacher_id']??0)),
            'start_date'=>$this->validDateFilter((string)($filters['start_date']??'')),
            'end_date'=>$this->validDateFilter((string)($filters['end_date']??'')),
            'risk'=>in_array($risk,['CRITICAL','HIGH','ATTENTION','LOW'],true)?$risk:'',
            'order'=>in_array($order,['end_desc','end_asc','student_asc','risk_desc'],true)?$order:'end_desc',
            'page'=>max(1,(int)($filters['page']??1)),
            'per_page'=>max(5,min(30,(int)($filters['per_page']??10))),
        ];
    }

    private function validDateFilter(string $date): string
    {
        $date=trim($date);
        return preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)===1?$date:'';
    }

    private function monitoringRiskLevel(array $item): string
    {
        $weight=0;
        foreach((array)($item['monitoring_insights']??[]) as $insight){$weight=max($weight,$this->riskWeight(strtoupper((string)($insight['level']??''))));}
        $absence=(int)($item['metrics']['unjustified_absences']??$item['metrics']['ranking_absences']??0);
        $occurrences=(int)($item['metrics']['total_occurrences']??0);
        if($absence>=8||$occurrences>=5)$weight=max($weight,4);
        elseif($absence>=5||$occurrences>=3)$weight=max($weight,3);
        elseif($absence>=2||$occurrences>=1)$weight=max($weight,2);
        return match($weight){4=>'CRITICAL',3=>'HIGH',2=>'ATTENTION',default=>'LOW'};
    }

    private function riskWeight(string $risk): int
    {
        return match(strtoupper($risk)){'CRITICAL','DANGER'=>4,'HIGH'=>3,'ATTENTION','WARNING'=>2,default=>1};
    }


    private function concludeExpiredLinks(int $userId): void
    {
        if($userId<=0) return;
        foreach($this->repository->expiredActiveLinksForUser($userId) as $link){
            $this->repository->concludeExpiredLink((int)$link['id']);
            $monitoringId=(int)($link['monitoring_id']??0);
            if($monitoringId>0 && $this->repository->closeCaseIfExpired($monitoringId)){
                $this->repository->recordEvent($monitoringId,'STATUS_CHANGED',null,$userId,0,'Status alterado automaticamente para COMPLETED após o fim do período.');
                $this->repository->recordEvent($monitoringId,'CASE_CLOSED',null,$userId,0,'Caso encerrado automaticamente ao término do período, sem participantes ativos.');
            }
            $endDate=(string)($link['end_date']??'');
            $this->notificationService->notifyUser(
                $userId,
                'MONITORING_COMPLETED',
                'Acompanhamento concluído: '.(string)$link['student_name'],
                'O período de acompanhamento do aluno '.(string)$link['student_name'].' foi concluído em '.($endDate!==''?date('d/m/Y',strtotime($endDate)):'data não informada').'. O histórico e as ações realizadas permanecem disponíveis para consulta.',
                'INFO',
                'STUDENT',
                (int)$link['student_id'],
                ['monitoring_id'=>(int)$link['monitoring_id'],'end_date'=>$endDate,'event_id'=>'monitoring_completed_'.$link['id'].'_'.$endDate],
                'monitoring_completed_'.$link['id'].'_'.$endDate
            );
        }
    }


    private function interventionComparison(int $studentId,string $start,string $end,array $actions): ?array
    {
        $action=$actions[0]??null;
        if(!$action||empty($action['action_date'])) return null;
        $actionDate=(string)$action['action_date'];
        if($actionDate<=$start||$actionDate>$end) return null;
        $beforeEnd=date('Y-m-d',strtotime($actionDate.' -1 day'));
        $before=$this->repository->periodMetrics($studentId,$start,$beforeEnd);
        $after=$this->repository->periodMetrics($studentId,$actionDate,$end);
        return ['action_date'=>$actionDate,'action_type_label'=>(string)($action['action_type_label']??'Ação'),'before'=>$before,'after'=>$after];
    }

    public function contextForStudent(int $studentId,int $userId,?int $selectedMonitoringId=null): array
    {
        $this->concludeExpiredLinks($userId);

        $cases=$this->repository->casesForStudent($studentId);
        $monitoring=null;
        if($selectedMonitoringId!==null){
            foreach($cases as $case){
                if((int)($case['id']??0)===$selectedMonitoringId){$monitoring=$case;break;}
            }
        }
        if($monitoring===null && $cases!==[]){
            foreach($cases as $case){
                if((string)($case['status']??'')==='ACTIVE' && (int)($case['active_followers']??0)>0){$monitoring=$case;break;}
            }
            $monitoring??=$cases[0];
        }

        $currentLink=$monitoring?$this->repository->userLink((int)$monitoring['id'],$userId):null;
        $activeLink=$monitoring?$this->repository->activeUserLink((int)$monitoring['id'],$userId):null;
        $periodStart=(string)($monitoring['start_date']??$currentLink['start_date']??'');
        $periodEnd=(string)($monitoring['end_date']??$currentLink['end_date']??'');
        $metrics=$monitoring&&$periodStart!==''&&$periodEnd!==''?$this->repository->periodMetrics($studentId,$periodStart,min($periodEnd,date('Y-m-d'))):[];
        $referenceStart=date('Y-01-01');
        $referenceMetrics=$this->repository->periodMetrics($studentId,$referenceStart,date('Y-m-d'));
        $caseReadings=$monitoring?$this->integratedCaseReadings($studentId,$monitoring,$currentLink,$periodStart,$periodEnd):[];
        $treatmentReading=$caseReadings['UNJUSTIFIED_ABSENCES']??null;
        $actionsSummary=$monitoring?$this->integratedActionsSummary((int)$monitoring['id']):['total'=>0,'latest'=>[],'by_issue'=>[]];
        $activeFollowersCount=$monitoring?$this->repository->activeFollowersCount((int)$monitoring['id']):0;
        $timeline=$monitoring?$this->repository->timelineForMonitoring((int)$monitoring['id']):[];
        if($monitoring && $timeline!==[]){
            $timelineActionIds=array_values(array_unique(array_filter(array_map(static fn(array $event):int=>(int)($event['action_id']??0),$timeline))));
            $timelineAttachments=$this->attachmentService->groupedByActions($timelineActionIds);
            foreach($timeline as &$timelineEvent){
                $timelineEvent['attachments']=$timelineAttachments[(int)($timelineEvent['action_id']??0)]??[];
            }
            unset($timelineEvent);
        }
        $latestAction=$actionsSummary['latest'][0]??null;
        $lastNoFollowers=null; foreach($timeline as $timelineEvent){if(($timelineEvent['type']??'')==='NO_ACTIVE_FOLLOWERS'){$lastNoFollowers=$timelineEvent;break;}}
        $followupSnapshot=['status'=>$activeFollowersCount>0?'ACTIVE':'EMPTY','active_followers'=>$activeFollowersCount,'latest_action'=>$latestAction,'without_followers_since'=>$activeFollowersCount===0?($lastNoFollowers['event_at']??null):null,'attendance_percentage'=>(float)($referenceMetrics['attendance_percentage']??0),'open_occurrences'=>(int)($referenceMetrics['open_occurrences']??0)];
        $currentRecommendation=$monitoring?$this->repository->currentRecommendation((int)$monitoring['id']):null;
        $plan=null;
        if($monitoring){
            if($currentLink){$plan=$this->repository->planForLink((int)$currentLink['id']);}
            $plan??=$this->repository->activePlanForMonitoring((int)$monitoring['id']);
        }

        $profileActions=$monitoring?$this->repository->actionsForMonitoring((int)$monitoring['id']):[];
        if($profileActions!==[]){
            $profileAttachments=$this->attachmentService->groupedByActions(array_column($profileActions,'id'));
            foreach($profileActions as &$profileAction){$profileAction['attachments']=$profileAttachments[(int)($profileAction['id']??0)]??[];}
            unset($profileAction);
        }

        $activeCases=0;$concludedCases=0;
        foreach($cases as &$case){
            $case['is_selected']=$monitoring!==null&&(int)$case['id']===(int)$monitoring['id'];
            $case['is_active']=(string)($case['status']??'')==='ACTIVE'&&(int)($case['active_followers']??0)>0;
            $caseEnd=(string)($case['end_date']??'');
            $caseStatus=strtoupper((string)($case['status']??''));
            if($case['is_active']){
                $case['display_status']='IN_PROGRESS';
                $case['display_status_label']='Em andamento';
                $activeCases++;
            }elseif(in_array($caseStatus,['RESOLVED','CLOSED','ARCHIVED'],true)){
                $case['display_status']=$caseStatus;
                $case['display_status_label']=$caseStatus==='RESOLVED'?'Resolvido':'Encerrado';
                $concludedCases++;
            }else{
                $case['display_status']='CONCLUDED';
                $case['display_status_label']='Concluído';
                $concludedCases++;
            }
            $case['sort_date']=(string)($case['closed_at']??$case['updated_at']??$case['created_at']??$caseEnd);
        }
        unset($case);

        return ['monitoring'=>$monitoring,'selected_case_id'=>(int)($monitoring['id']??0),'cases'=>$cases,'active_cases_count'=>$activeCases,'concluded_cases_count'=>$concludedCases,'current_link'=>$currentLink,'users'=>$monitoring?$this->repository->users((int)$monitoring['id']):[],'teachers'=>Roles::hasFullAccess((string)Authorization::role())?$this->repository->teachers():[],'can_assign_others'=>Roles::hasFullAccess((string)Authorization::role()),'current_user_id'=>$userId,'is_following'=>$activeLink!==null,'has_monitoring_history'=>$currentLink!==null,'is_concluded'=>$monitoring!==null&&$activeFollowersCount===0,'reason_options'=>self::REASON_OPTIONS,'objective_options'=>self::OBJECTIVE_OPTIONS,'strategy_options'=>self::STRATEGY_OPTIONS,'plan'=>$plan,'metrics'=>$metrics,'reference_metrics'=>$referenceMetrics,'reference_start'=>$referenceStart,'suggestions'=>$monitoring?$this->suggestions($studentId,$metrics):[],'extensions'=>$monitoring?$this->repository->extensionsForMonitoring((int)$monitoring['id']):[],'treatment_reading'=>$treatmentReading,'case_readings'=>$caseReadings,'actions_summary'=>$actionsSummary,'actions'=>$profileActions,'monitoring_timeline'=>$timeline,'active_followers_count'=>$activeFollowersCount,'followup_snapshot'=>$followupSnapshot,'current_recommendation'=>$currentRecommendation,'is_management'=>Roles::hasFullAccess((string)Authorization::role()),'action_type_options'=>self::ACTION_TYPE_OPTIONS,'issue_type_options'=>self::ISSUE_TYPE_OPTIONS];
    }


    private function integratedActionsSummary(int $monitoringId): array
    {
        $actions=$this->repository->actionsForMonitoring($monitoringId);
        $byIssue=['GENERAL'=>0,'UNJUSTIFIED_ABSENCES'=>0,'OCCURRENCE'=>0];
        foreach($actions as $action){
            $issue=(string)($action['issue_type']??'GENERAL');
            if(!isset($byIssue[$issue]))$issue='GENERAL';
            $byIssue[$issue]++;
        }
        return [
            'total'=>count($actions),
            'latest'=>array_slice($actions,0,5),
            'by_issue'=>$byIssue,
        ];
    }


    private function integratedCaseReadings(int $studentId,array $monitoring,?array $currentLink,string $periodStart,string $periodEnd): array
    {
        $actions=$this->repository->actionsForMonitoring((int)$monitoring['id']);
        $end=min($periodEnd!==''?$periodEnd:date('Y-m-d'),date('Y-m-d'));
        $current=$periodStart!==''?$this->repository->periodMetrics($studentId,$periodStart,$end):[];
        $readings=[];

        $absenceActions=array_values(array_filter($actions,static fn(array $action):bool=>(string)($action['issue_type']??'')==='UNJUSTIFIED_ABSENCES'));
        $unjustified=(int)($current['total_faltas']??0);
        if($unjustified>0||$absenceActions!==[]){
            $readings['UNJUSTIFIED_ABSENCES']=$this->buildAbsenceReading($studentId,$periodStart,$end,$unjustified,$absenceActions[0]??null,$currentLink);
        }

        $occurrenceActions=array_values(array_filter($actions,static fn(array $action):bool=>(string)($action['issue_type']??'')==='OCCURRENCE'));
        $totalOccurrences=(int)($current['total_occurrences']??0);
        $openOccurrences=(int)($current['open_occurrences']??0);
        if($totalOccurrences>0||$occurrenceActions!==[]){
            $readings['OCCURRENCE']=$this->buildOccurrenceReading($studentId,$periodStart,$end,$openOccurrences,$occurrenceActions[0]??null,$currentLink);
        }

        $generalActions=array_values(array_filter($actions,static fn(array $action):bool=>in_array((string)($action['issue_type']??'GENERAL'),['','GENERAL'],true)));
        $readings['GENERAL']=$this->buildGeneralReading($studentId,$periodStart,$end,$current,$generalActions[0]??null);
        return $readings;
    }

    private function actionPeriods(int $studentId,string $periodStart,string $end,?array $action): array
    {
        if(!$action||empty($action['action_date'])) return ['before'=>[],'after'=>[],'after_records'=>0,'days_after'=>0];
        $actionDate=(string)$action['action_date'];
        $beforeEnd=date('Y-m-d',strtotime($actionDate.' -1 day'));
        $afterStart=date('Y-m-d',strtotime($actionDate.' +1 day'));
        $before=$periodStart!==''&&$beforeEnd>=$periodStart?$this->repository->periodMetrics($studentId,$periodStart,$beforeEnd):[];
        $after=$afterStart<=$end?$this->repository->periodMetrics($studentId,$afterStart,$end):[];
        $daysAfter=max(0,(int)((new \DateTimeImmutable($actionDate))->diff(new \DateTimeImmutable($end))->format('%r%a')));
        return ['before'=>$before,'after'=>$after,'after_records'=>(int)($after['total_records']??0),'days_after'=>$daysAfter];
    }

    private function buildAbsenceReading(int $studentId,string $periodStart,string $end,int $unjustified,?array $latest,?array $currentLink): array
    {
        if(!$latest){
            return ['issue'=>'UNJUSTIFIED_ABSENCES','label'=>'Faltas sem justificativa','state'=>'NOT_STARTED','level'=>'ATTENTION','title'=>'Intervenção necessária','message'=>$unjustified.' falta(s) sem justificativa foram registradas no período e ainda não há uma intervenção vinculada a esse motivo.','current_status'=>'Aguardando primeira ação','guidance'=>'Registre a providência realizada, como conversa com o aluno, contato com responsáveis ou encaminhamento.','latest_action'=>null,'verification_status'=>'PENDING','can_reopen'=>false];
        }
        $periods=$this->actionPeriods($studentId,$periodStart,$end,$latest);
        $before=(array)$periods['before']; $after=(array)$periods['after'];
        $afterRecords=(int)$periods['after_records']; $daysAfter=(int)$periods['days_after'];
        $afterAbsences=(int)($after['total_faltas']??0);
        $beforeFrequency=(float)($before['attendance_percentage']??0); $afterFrequency=(float)($after['attendance_percentage']??0);
        $actionLabel=(string)($latest['action_type_label']??'Ação registrada');
        $base=['issue'=>'UNJUSTIFIED_ABSENCES','label'=>'Faltas sem justificativa','latest_action'=>$latest,'before'=>$before,'after'=>$after];

        if($afterRecords<5||$daysAfter<5){
            return $base+['state'=>'INTERVENTION','level'=>'INFO','title'=>'Intervenção realizada','message'=>'A ação “'.$actionLabel.'” foi registrada para tratar as faltas sem justificativa.','current_status'=>'Em observação','guidance'=>'Aguarde novos registros de frequência para que o sistema avalie a resposta à intervenção.','verification_status'=>'OBSERVATION'];
        }
        if($afterAbsences===0&&($beforeFrequency<=0||$afterFrequency>=$beforeFrequency)){
            return $base+['state'=>'POSITIVE_EVOLUTION','level'=>'POSITIVE','title'=>'Evolução positiva','message'=>'Após as intervenções, não foram registradas novas faltas sem justificativa e a frequência se manteve estável ou melhorou.','current_status'=>'Resolução confirmada pelos dados','guidance'=>'Continue acompanhando a frequência para confirmar a manutenção da melhora.','verification_status'=>'CONFIRMED'];
        }
        if($afterAbsences>0&&$beforeFrequency>0&&$afterFrequency>=$beforeFrequency+3){
            return $base+['state'=>'PARTIAL_EVOLUTION','level'=>'INFO','title'=>'Melhora parcial','message'=>'A frequência melhorou após a intervenção, mas ainda existem '.$afterAbsences.' falta(s) sem justificativa nos registros posteriores.','current_status'=>'Em observação com evolução parcial','guidance'=>'Mantenha a estratégia e verifique se as faltas deixam de se repetir.','verification_status'=>'PARTIAL'];
        }
        return $base+['state'=>'NOT_CONFIRMED','level'=>'ATTENTION','title'=>'Intervenção sem resposta confirmada','message'=>'Após a ação “'.$actionLabel.'”, os registros posteriores ainda apresentam '.$afterAbsences.' falta(s) sem justificativa.','current_status'=>'Infrequência persistente','guidance'=>'Reavalie a estratégia, registre uma nova providência e mantenha o histórico da intervenção anterior.','verification_status'=>'NOT_CONFIRMED','can_reopen'=>$currentLink!==null];
    }

    private function buildOccurrenceReading(int $studentId,string $periodStart,string $end,int $openOccurrences,?array $latest,?array $currentLink): array
    {
        if(!$latest){
            return ['issue'=>'OCCURRENCE','label'=>'Ocorrências','state'=>'NOT_STARTED','level'=>'ATTENTION','title'=>'Intervenção necessária','message'=>$openOccurrences.' ocorrência(s) permanecem abertas e ainda não há uma ação de acompanhamento vinculada a esse motivo.','current_status'=>'Aguardando primeira ação','guidance'=>'Registre a providência adotada e acompanhe a resolução das pendências e possíveis reincidências.','latest_action'=>null,'verification_status'=>'PENDING','can_reopen'=>false];
        }
        $periods=$this->actionPeriods($studentId,$periodStart,$end,$latest);
        $after=(array)$periods['after']; $afterRecords=(int)$periods['after_records']; $daysAfter=(int)$periods['days_after'];
        $afterOpen=(int)($after['open_occurrences']??0); $afterTotal=(int)($after['total_occurrences']??0);
        $actionLabel=(string)($latest['action_type_label']??'Ação registrada');
        $base=['issue'=>'OCCURRENCE','label'=>'Ocorrências','latest_action'=>$latest,'before'=>$periods['before'],'after'=>$after];
        if($afterRecords<5||$daysAfter<5){
            return $base+['state'=>'INTERVENTION','level'=>'INFO','title'=>'Intervenção realizada','message'=>'A ação “'.$actionLabel.'” foi registrada para tratar as ocorrências do aluno.','current_status'=>'Em observação','guidance'=>'Acompanhe a resolução das ocorrências abertas e verifique se surgem novos registros.','verification_status'=>'OBSERVATION'];
        }
        if($afterOpen===0&&$afterTotal===0){
            return $base+['state'=>'POSITIVE_EVOLUTION','level'=>'POSITIVE','title'=>'Evolução positiva','message'=>'Não foram registradas novas ocorrências após as intervenções realizadas.','current_status'=>'Evolução confirmada pelos dados','guidance'=>'Continue observando para confirmar a manutenção sem reincidência.','verification_status'=>'CONFIRMED'];
        }
        return $base+['state'=>'NOT_CONFIRMED','level'=>'ATTENTION','title'=>'Intervenção sem resposta confirmada','message'=>'Após a ação “'.$actionLabel.'”, ainda existem ocorrências abertas ou novos registros posteriores.','current_status'=>'Situação ainda requer atenção','guidance'=>'Revise a estratégia e registre a próxima providência adotada.','verification_status'=>'NOT_CONFIRMED','can_reopen'=>$currentLink!==null];
    }

    private function buildGeneralReading(int $studentId,string $periodStart,string $end,array $current,?array $latest): array
    {
        if(!$latest){
            return ['issue'=>'GENERAL','label'=>'Acompanhamento individual','state'=>'NOT_STARTED','level'=>'INFO','title'=>'Acompanhamento iniciado','message'=>'O aluno possui acompanhamento ativo, mas ainda não há uma intervenção geral registrada.','current_status'=>'Aguardando primeira ação','guidance'=>'Registre a primeira providência realizada para iniciar a avaliação da evolução.','latest_action'=>null,'verification_status'=>'PENDING','can_reopen'=>false];
        }
        $periods=$this->actionPeriods($studentId,$periodStart,$end,$latest);
        $before=(array)$periods['before']; $after=(array)$periods['after'];
        $afterRecords=(int)$periods['after_records']; $daysAfter=(int)$periods['days_after'];
        $beforeFrequency=(float)($before['attendance_percentage']??0); $afterFrequency=(float)($after['attendance_percentage']??0);
        $beforeOpen=(int)($before['open_occurrences']??0); $afterOpen=(int)($after['open_occurrences']??0);
        $actionLabel=(string)($latest['action_type_label']??'Ação registrada');
        $base=['issue'=>'GENERAL','label'=>'Acompanhamento individual','latest_action'=>$latest,'before'=>$before,'after'=>$after];
        if($afterRecords<5||$daysAfter<5){
            return $base+['state'=>'INTERVENTION','level'=>'INFO','title'=>'Intervenção realizada','message'=>'A ação “'.$actionLabel.'” foi registrada no acompanhamento individual do aluno.','current_status'=>'Em observação','guidance'=>'Aguarde novos registros para que o sistema avalie a evolução após a intervenção.','verification_status'=>'OBSERVATION'];
        }
        $frequencyImproved=$beforeFrequency>0&&$afterFrequency>=$beforeFrequency+3;
        $occurrencesImproved=$beforeOpen>0&&$afterOpen===0;
        if($frequencyImproved||$occurrencesImproved){
            $details=[];
            if($frequencyImproved)$details[]='a frequência apresentou melhora';
            if($occurrencesImproved)$details[]='as ocorrências abertas foram resolvidas';
            return $base+['state'=>'POSITIVE_EVOLUTION','level'=>'POSITIVE','title'=>'Evolução positiva','message'=>'Após as intervenções, '.implode(' e ',$details).'.','current_status'=>'Resposta positiva confirmada pelos dados','guidance'=>'Mantenha as estratégias adotadas e continue observando a estabilidade da evolução.','verification_status'=>'CONFIRMED'];
        }
        return $base+['state'=>'IN_PROGRESS','level'=>'INFO','title'=>'Acompanhamento em andamento','message'=>'A ação “'.$actionLabel.'” foi realizada e os indicadores posteriores ainda não permitem confirmar uma mudança consistente.','current_status'=>'Em observação','guidance'=>'Continue acompanhando os dados e registre apenas novas providências ou mudanças de estratégia.','verification_status'=>'IN_PROGRESS'];
    }

    public function integratedTreatmentReading(int $studentId,int $userId,array $monitoring,?array $currentLink,string $periodStart,string $periodEnd): ?array
    {
        $readings=$this->integratedCaseReadings($studentId,$monitoring,$currentLink,$periodStart,$periodEnd);
        return $readings['UNJUSTIFIED_ABSENCES']??null;
    }

    public function reopenAbsenceTreatment(array $data,array $user): void
    {
        $monitoringId=(int)($data['monitoring_id']??0);$userId=(int)($user['id']??0);
        $link=$this->repository->activeUserLink($monitoringId,$userId);
        if(!$link) throw new InvalidArgumentException('Você precisa estar acompanhando este aluno para reabrir o tratamento.');
        $reason=trim((string)($data['reason']??'Persistência da infrequência identificada pelos registros posteriores.'));
        $this->repository->createAction(['monitoring_id'=>$monitoringId,'action_date'=>date('Y-m-d'),'action_type'=>'OTHER','action_type_label'=>'Tratamento reaberto','description'=>$reason,'result_status'=>null,'issue_type'=>'UNJUSTIFIED_ABSENCES','treatment_status'=>null,'next_action'=>'Registrar nova providência e revisar a frequência nos próximos dias letivos.','created_by'=>$userId]);
    }

    public function start(array $data,array $user): int
    {
        $studentId=(int)($data['student_id']??0); $userId=(int)($user['id']??0); $start=trim((string)($data['start_date']??date('Y-m-d'))); $end=trim((string)($data['end_date']??''));
        if($studentId<=0||$userId<=0) throw new InvalidArgumentException('Aluno ou usuário inválido.');
        if(!$this->validDate($start)||!$this->validDate($end)||$end<$start) throw new InvalidArgumentException('Informe um período válido para o acompanhamento.');

        $reasonCode=trim((string)($data['reason_code']??''));
        $reasonLabel=self::REASON_OPTIONS[$reasonCode]??'';
        if($reasonLabel==='') throw new InvalidArgumentException('Selecione o motivo do acompanhamento.');
        $reasonDetails=trim((string)($data['reason_details']??''));
        $reason=$reasonLabel.($reasonDetails!==''?' — '.$reasonDetails:'');

        // O plano é parte obrigatória do acompanhamento. Nenhum vínculo pode ser
        // criado ou reativado sem objetivo e pelo menos uma estratégia válida.
        $objectiveCode=trim((string)($data['objective_code']??''));
        $objectiveLabel=self::OBJECTIVE_OPTIONS[$objectiveCode]??'';
        if($objectiveLabel==='') throw new InvalidArgumentException('Selecione o objetivo principal do plano de acompanhamento.');
        $strategyCodes=[];
        foreach((array)($data['strategies']??[]) as $code){$code=(string)$code;if(isset(self::STRATEGY_OPTIONS[$code]))$strategyCodes[]=$code;}
        $strategyCodes=array_values(array_unique($strategyCodes));
        if($strategyCodes===[]) throw new InvalidArgumentException('Selecione ao menos uma estratégia para iniciar o acompanhamento.');

        $targetMetric=trim((string)($data['target_metric']??'NONE'));
        if(!in_array($targetMetric,['ATTENDANCE','OCCURRENCES','NONE'],true))$targetMetric='NONE';
        $target=$this->nullableNumber($data['target_value']??null);
        if($targetMetric==='ATTENDANCE'&&$target!==null&&($target<0||$target>100)) throw new InvalidArgumentException('A meta de frequência deve estar entre 0 e 100%.');
        if($targetMetric==='OCCURRENCES'&&$target!==null&&($target<0||floor($target)!=$target)) throw new InvalidArgumentException('A meta de ocorrências deve ser um número inteiro igual ou maior que zero.');

        $isManagement=Roles::hasFullAccess((string)($user['role']??''));
        $targetIds=[];
        // Quem executa a operação não se torna acompanhante automaticamente.
        // Professores comuns acompanham a si mesmos; gestores só entram quando marcam explicitamente essa opção.
        if(!$isManagement || (string)($data['follow_self']??'')==='1') $targetIds[]=$userId;
        if($isManagement){foreach((array)($data['teacher_ids']??[]) as $id){if((int)$id>0)$targetIds[]=(int)$id;}}
        $targetIds=array_values(array_unique($targetIds));
        if($targetIds===[]) throw new InvalidArgumentException('Selecione ao menos uma pessoa para acompanhar o aluno.');

        // Cada início cria um caso independente. O mesmo aluno pode possuir vários
        // acompanhamentos simultâneos, com problemas, planos e participantes distintos.
        $caseTitle=trim((string)($data['case_title']??''));
        if($caseTitle==='') $caseTitle=$reasonLabel;
        if(mb_strlen($caseTitle)>180) $caseTitle=mb_substr($caseTitle,0,180);
        $monitoringId=$this->repository->create([
            'student_id'=>$studentId,
            'case_title'=>$caseTitle,
            'problem_code'=>$reasonCode,
            'problem_details'=>$reasonDetails!==''?$reasonDetails:null,
            'start_date'=>$start,
            'end_date'=>$end,
            'reason'=>$reason,
            'created_by'=>$userId,
        ]);
        $this->repository->recordEvent($monitoringId,'CASE_CREATED',null,$userId,0,'Caso criado: '.$caseTitle.'. Motivo: '.$reason);
        $metrics=$this->repository->periodMetrics($studentId,$start,min($end,date('Y-m-d')));
        $baseline=null;
        if($targetMetric==='ATTENDANCE'&&(int)($metrics['total_records']??0)>0)$baseline=(float)$metrics['attendance_percentage'];
        elseif($targetMetric==='OCCURRENCES')$baseline=(float)($metrics['total_occurrences']??0);

        foreach($targetIds as $id){
            $alreadyActive=$this->repository->activeUserLink($monitoringId,$id)!==null;
            $activeBefore=$this->repository->activeFollowersCount($monitoringId);
            $linkId=$this->repository->addUser($monitoringId,$id,$userId,$start,$end,null);
            if(!$alreadyActive){
                $activeAfter=$this->repository->activeFollowersCount($monitoringId);
                $eventType='PARTICIPANT_ADDED';
                $this->repository->recordEvent($monitoringId,$eventType,$id,$userId,$activeAfter,$reason);
            }
        }
        $this->repository->savePlan($monitoringId,[
            'objective_code'=>$objectiveCode,
            'objective_label'=>$objectiveLabel,
            'strategies'=>$strategyCodes,
            'target_metric'=>$targetMetric,
            'baseline_value'=>$baseline,
            'target_value'=>$target,
            'notes'=>trim((string)($data['notes']??''))?:null,
            'created_by'=>$userId,
        ]);
        $this->repository->recordEvent($monitoringId,'PLAN_CREATED',null,$userId,$this->repository->activeFollowersCount($monitoringId),'Plano inicial do caso criado.');
        $alertType=trim((string)($data['alert_type']??''));
        if($alertType!=='') $this->intelligenceService->linkAlertToCase($studentId,$monitoringId,$alertType,$userId);
        return $monitoringId;
    }


    public function addParticipants(array $data, array $user): void
    {
        if (!Roles::hasFullAccess((string)($user['role'] ?? ''))) {
            throw new InvalidArgumentException('Você não possui permissão para adicionar acompanhantes.');
        }

        $monitoringId=(int)($data['monitoring_id']??0);
        $userId=(int)($user['id']??0);
        $monitoring=$this->repository->find($monitoringId);
        if(!$monitoring||($monitoring['status']??'')!=='ACTIVE') {
            throw new InvalidArgumentException('Acompanhamento ativo não encontrado.');
        }

        $allowed=array_map('intval',array_column($this->repository->teachers(),'id'));
        $teacherIds=[];
        foreach((array)($data['teacher_ids']??[]) as $id){
            $id=(int)$id;
            if($id>0&&in_array($id,$allowed,true)) $teacherIds[]=$id;
        }
        $teacherIds=array_values(array_unique($teacherIds));
        if($teacherIds===[]) throw new InvalidArgumentException('Selecione ao menos um novo acompanhante.');

        $start=trim((string)($data['start_date']??$data['participant_start_date']??$monitoring['start_date']));
        $end=trim((string)($data['end_date']??$data['participant_end_date']??$monitoring['end_date']));
        if(!$this->validDate($start)||!$this->validDate($end)||$end<$start) {
            throw new InvalidArgumentException('Informe um período válido para o novo acompanhante.');
        }
        if($start<(string)$monitoring['start_date']||$end>(string)$monitoring['end_date']) {
            throw new InvalidArgumentException('O período do novo acompanhante deve estar dentro do período do acompanhamento atual.');
        }

        $internalNotes=trim((string)($data['internal_notes']??$data['participant_notes']??''));
        if(mb_strlen($internalNotes)>3000) throw new InvalidArgumentException('As observações internas estão muito longas.');

        foreach($teacherIds as $teacherId){
            $alreadyActive=$this->repository->activeUserLink($monitoringId,$teacherId)!==null;
            if($alreadyActive) continue;

            $activeBefore=$this->repository->activeFollowersCount($monitoringId);
            $linkId=$this->repository->addUser($monitoringId,$teacherId,$userId,$start,$end,$internalNotes!==''?$internalNotes:null);
            $activeAfter=$this->repository->activeFollowersCount($monitoringId);
            if($activeBefore===0){
                $this->repository->updateCaseStatus($monitoringId,'ACTIVE');
                $this->repository->recordEvent($monitoringId,'CASE_REOPENED',null,$userId,$activeAfter,'Caso reaberto com a entrada de um novo participante.');
                $this->repository->recordEvent($monitoringId,'STATUS_CHANGED',null,$userId,$activeAfter,'Status alterado para ACTIVE.');
            }
            $this->repository->recordEvent($monitoringId,'PARTICIPANT_ADDED',$teacherId,$userId,$activeAfter,$internalNotes!==''?$internalNotes:null);


            $targetName=$this->repository->userNameById($teacherId) ?? 'Novo acompanhante';
            $this->notifyMonitoringEvent(
                $monitoring,
                $user,
                'MONITORING_PARTICIPANT_ADDED',
                'Novo acompanhante: '.(string)$monitoring['student_name'],
                $targetName.' passou a acompanhar o aluno.',
                'INFO',
                ['target_user_id'=>$teacherId,'event_key'=>'participant_added_'.$linkId],
                false
            );
        }
    }

    public function savePlan(array $data, array $user): void
    {
        $monitoringId=(int)($data['monitoring_id']??0);
        $userId=(int)($user['id']??0);
        if($monitoringId<=0||$userId<=0) throw new InvalidArgumentException('Acompanhamento inválido.');
        $link=$this->repository->activeUserLink($monitoringId,$userId);
        if(!$link) throw new InvalidArgumentException('Você precisa acompanhar este aluno para criar um plano.');

        $objectiveCode=trim((string)($data['objective_code']??''));
        $objectiveLabel=self::OBJECTIVE_OPTIONS[$objectiveCode]??'';
        if($objectiveLabel==='') throw new InvalidArgumentException('Selecione um objetivo válido.');

        $strategyCodes=[];
        foreach((array)($data['strategies']??[]) as $code){$code=(string)$code;if(isset(self::STRATEGY_OPTIONS[$code]))$strategyCodes[]=$code;}
        $strategyCodes=array_values(array_unique($strategyCodes));
        if($strategyCodes===[]) throw new InvalidArgumentException('Selecione ao menos uma estratégia.');

        $targetMetric=trim((string)($data['target_metric']??''));
        if(!in_array($targetMetric,['ATTENDANCE','OCCURRENCES','NONE'],true))$targetMetric='NONE';
        // O valor inicial nunca é digitado manualmente: ele é obtido dos dados
        // oficiais do aluno dentro do período deste vínculo de acompanhamento.
        $monitoring=$this->repository->find($monitoringId);
        if(!$monitoring) throw new InvalidArgumentException('Acompanhamento não encontrado.');
        $periodStart=(string)$monitoring['start_date'];
        $periodEnd=min((string)$monitoring['end_date'],date('Y-m-d'));
        $metrics=$this->repository->periodMetrics((int)$monitoring['student_id'],$periodStart,$periodEnd);
        $baseline=null;
        if($targetMetric==='ATTENDANCE' && (int)($metrics['total_records']??0)>0){
            $baseline=(float)$metrics['attendance_percentage'];
        } elseif($targetMetric==='OCCURRENCES'){
            $baseline=(float)($metrics['total_occurrences']??0);
        }

        $target=$this->nullableNumber($data['target_value']??null);
        if($targetMetric==='ATTENDANCE'&&$target!==null&&($target<0||$target>100)) throw new InvalidArgumentException('A meta de frequência deve estar entre 0 e 100%.');
        if($targetMetric==='OCCURRENCES'&&$target!==null&&($target<0||floor($target)!=$target)) throw new InvalidArgumentException('A meta de ocorrências deve ser um número inteiro igual ou maior que zero.');

        $existingPlan=$this->repository->activePlanForMonitoring($monitoringId);
        $this->repository->savePlan($monitoringId,[
            'objective_code'=>$objectiveCode,
            'objective_label'=>$objectiveLabel,
            'strategies'=>$strategyCodes,
            'target_metric'=>$targetMetric,
            'baseline_value'=>$baseline,
            'target_value'=>$target,
            'notes'=>trim((string)($data['notes']??''))?:null,
            'created_by'=>$userId,
        ]);
        if (!$existingPlan) {
            $this->repository->recordEvent($monitoringId,'PLAN_CREATED',null,$userId,$this->repository->activeFollowersCount($monitoringId),'Plano compartilhado do caso criado.');
        }
    }

    private function nullableNumber(mixed $value): ?float
    {
        if($value===null||$value==='')return null;
        if(!is_numeric($value))throw new InvalidArgumentException('Informe valores numéricos válidos para a meta.');
        return round((float)$value,2);
    }


    public function extend(array $data, array $user): void
    {
        $monitoringId=(int)($data['monitoring_id']??0);
        $userId=(int)($user['id']??0);
        $monitoring=$this->repository->find($monitoringId);
        if(!$monitoring||($monitoring['status']??'')!=='ACTIVE') throw new InvalidArgumentException('Caso ativo não encontrado para prorrogação.');
        if(!$this->repository->activeUserLink($monitoringId,$userId) && !Roles::hasFullAccess((string)($user['role']??''))) throw new InvalidArgumentException('Você não possui permissão para prorrogar este caso.');
        $newEnd=trim((string)($data['new_end_date']??''));
        $reason=trim((string)($data['extension_reason']??''));
        if(!$this->validDate($newEnd)||$newEnd<=(string)$monitoring['end_date']) throw new InvalidArgumentException('A nova data final deve ser posterior à data atual do acompanhamento.');
        if($reason==='') throw new InvalidArgumentException('Informe o motivo da prorrogação.');
        if(mb_strlen($reason)>2000) throw new InvalidArgumentException('O motivo da prorrogação está muito longo.');
        $this->repository->extendMonitoring($monitoringId,$newEnd,$reason,$userId);
        $this->repository->recordEvent($monitoringId,'PERIOD_EXTENDED',null,$userId,$this->repository->activeFollowersCount($monitoringId),'Período prorrogado até '.$newEnd.'. Motivo: '.$reason);
    }


    private function notifyMonitoringEvent(
        array $monitoring,
        array $user,
        string $type,
        string $title,
        string $message,
        string $severity = 'INFO',
        array $metadata = [],
        bool $excludeActor = true
    ): void {
        $studentId = (int) ($monitoring['student_id'] ?? 0);
        if ($studentId <= 0) return;
        $this->notificationService->notifyMonitoringFollowers(
            $studentId,
            (string) ($monitoring['student_name'] ?? 'Aluno'),
            $type,
            $title,
            $message,
            $severity,
            $metadata,
            $excludeActor ? (int) ($user['id'] ?? 0) : null,
            (string) ($metadata['event_key'] ?? $type)
        );
    }

    public function stop(int $monitoringId,array $user,int $targetUserId=0): void
    {
        $userId=(int)($user['id']??0); if($monitoringId<=0||$userId<=0)return;
        $target=$userId;
        if(Roles::hasFullAccess((string)($user['role']??''))&&$targetUserId>0)$target=$targetUserId;
        if(!$this->repository->activeUserLink($monitoringId,$target)) return;
        $this->repository->endUser($monitoringId,$target);
        $activeAfter=$this->repository->activeFollowersCount($monitoringId);
        $this->repository->recordEvent($monitoringId,'PARTICIPANT_REMOVED',$target,$userId,$activeAfter,null);
        $monitoring=$this->repository->find($monitoringId);
        $targetName=$this->repository->userNameById($target) ?? 'Um acompanhante';
        if($monitoring){
            $this->notifyMonitoringEvent($monitoring,$user,'MONITORING_PARTICIPANT_REMOVED','Acompanhante removido: '.(string)$monitoring['student_name'],$targetName.' deixou de acompanhar o aluno.','WARNING',['event_key'=>'participant_removed_'.time(),'target_user_id'=>$target]);
        }
        if($activeAfter===0){
            $this->repository->recordEvent($monitoringId,'NO_ACTIVE_FOLLOWERS',null,$userId,0,'O aluno ficou sem nenhum acompanhante ativo.');
        }
    }

    private function isUserLinked(int $monitoringId,int $userId): bool{foreach($this->repository->users($monitoringId) as $u){if((int)$u['user_id']===$userId&&($u['status']??'')==='ACTIVE')return true;}return false;}
    private function validDate(string $date): bool{$d=\DateTime::createFromFormat('Y-m-d',$date);return $d&&$d->format('Y-m-d')===$date;}

    public function suggestions(int $studentId,array $metrics): array
    {
        $dashboard=$this->intelligenceService->studentDashboard($studentId); $suggestions=[]; $attendance=(float)($metrics['attendance_percentage']??0); $open=(int)($metrics['open_occurrences']??0); $occ=(int)($metrics['total_occurrences']??0);
        if($attendance>0&&$attendance<75)$suggestions[]=['level'=>'HIGH','title'=>'Realizar contato com o responsável','reason'=>'A frequência no período está abaixo de 75%.','action'=>'Registrar o contato e combinar uma estratégia de retorno.'];
        elseif($attendance>0&&$attendance<85)$suggestions[]=['level'=>'ATTENTION','title'=>'Conversar individualmente com o aluno','reason'=>'A frequência ainda exige atenção.','action'=>'Investigar dificuldades e estabelecer uma meta para as próximas semanas.'];
        elseif($attendance>=90)$suggestions[]=['level'=>'POSITIVE','title'=>'Reconhecer a evolução do aluno','reason'=>'A frequência no período está em nível positivo.','action'=>'Dar devolutiva positiva e reforçar a continuidade.'];
        if($open>0)$suggestions[]=['level'=>'HIGH','title'=>'Revisar ocorrências pendentes','reason'=>"Há {$open} ocorrência(s) ainda não resolvida(s).",'action'=>'Abrir o perfil, verificar providências existentes e alinhar próximos passos.'];
        elseif($occ>0)$suggestions[]=['level'=>'POSITIVE','title'=>'Consolidar os avanços comportamentais','reason'=>'As ocorrências do período não estão pendentes.','action'=>'Conversar com o aluno sobre o que contribuiu para a melhora.'];
        if($suggestions===[])$suggestions[]=['level'=>'INFO','title'=>'Definir uma meta de acompanhamento','reason'=>'Ainda há poucos dados no período escolhido.','action'=>'Registrar uma meta simples e revisar frequência e ocorrências semanalmente.'];
        return array_slice($suggestions,0,4);
    }

    public function createAction(array $data, array $user): int
    {
        $monitoringId=(int)($data['monitoring_id']??0);
        $userId=(int)($user['id']??0);
        $link=$this->repository->activeUserLink($monitoringId,$userId);
        if(!$link) throw new InvalidArgumentException('Você precisa estar acompanhando este aluno para registrar uma ação.');
        $validated=$this->validateActionData($data);
        $actionId=$this->repository->createAction($validated+['monitoring_id'=>$monitoringId,'created_by'=>$userId]);
        $this->repository->completeCurrentRecommendation($monitoringId,$userId,'Atendida automaticamente pelo registro da ação #'.$actionId.'.');
        $monitoring=$this->repository->find($monitoringId);
        if($monitoring){
            $actorName=trim((string)($user['name']??'Responsável'));
            $this->notifyMonitoringEvent($monitoring,$user,'MONITORING_ACTION_CREATED','Nova ação: '.(string)$monitoring['student_name'],$actorName.' registrou a ação “'.$validated['action_type_label'].'” no acompanhamento.','INFO',['action_id'=>$actionId,'event_key'=>'action_created_'.$actionId]);
        }
        return $actionId;
    }

    public function updateAction(array $data, array $user): void
    {
        $actionId=(int)($data['action_id']??0);
        $action=$this->repository->findAction($actionId);
        if(!$action) throw new InvalidArgumentException('Ação não encontrada.');
        $this->assertCanManageAction($action,$user);
        if(!$this->repository->isMonitoringOpen((int)$action['monitoring_id'])) throw new InvalidArgumentException('Este acompanhamento foi concluído. As ações históricas não podem mais ser alteradas.');
        $validated=$this->validateActionData($data);
        $this->repository->updateAction($actionId,$validated,(int)($user['id']??0));
    }

    public function deleteAction(int $actionId, array $user): void
    {
        $action=$this->repository->findAction($actionId);
        if(!$action) throw new InvalidArgumentException('Ação não encontrada.');
        $this->assertCanManageAction($action,$user);
        if(!$this->repository->isMonitoringOpen((int)$action['monitoring_id'])) throw new InvalidArgumentException('Este acompanhamento foi concluído. As ações históricas não podem ser excluídas.');
        $this->attachmentService->removeAll($actionId);
        $this->repository->softDeleteAction($actionId,(int)($user['id']??0));
        $monitoring=$this->repository->find((int)$action['monitoring_id']);
        if($monitoring){
            $this->notifyMonitoringEvent($monitoring,$user,'MONITORING_ACTION_DELETED','Ação excluída: '.(string)$monitoring['student_name'],'A ação “'.(string)($action['action_type_label']??'Ação').'” foi excluída do acompanhamento.','WARNING',['action_id'=>$actionId,'event_key'=>'action_deleted_'.$actionId]);
        }
    }

    public function uploadActionAttachments(int $actionId, array $files, array $user): array
    {
        $action=$this->repository->findAction($actionId);
        if(!$action) throw new InvalidArgumentException('Ação não encontrada para anexar arquivos.');
        $saved=$this->attachmentService->uploadMany($actionId,$files,(int)($user['id']??0),(string)($user['name']??''));
        foreach($saved as $attachmentId){
            $this->repository->recordEvent((int)$action['monitoring_id'],'ATTACHMENT_ADDED',null,(int)($user['id']??0),$this->repository->activeFollowersCount((int)$action['monitoring_id']),'Anexo #'.(int)$attachmentId.' adicionado à ação #'.$actionId.'.');
        }
        return $saved;
    }

    public function removeActionAttachment(int $attachmentId, array $user): bool
    {
        $attachment=$this->attachmentService->find($attachmentId);
        if(!$attachment) throw new InvalidArgumentException('Anexo não encontrado.');
        $this->assertCanManageAction(['created_by'=>(int)($attachment['action_created_by']??0)],$user);
        $monitoringId=(int)($attachment['monitoring_id']??0);
        if(!$this->repository->isMonitoringOpen($monitoringId)) throw new InvalidArgumentException('Este acompanhamento foi concluído. Os anexos históricos não podem ser excluídos.');
        $removed=$this->attachmentService->remove($attachmentId);
        if($removed){
            $this->repository->recordEvent($monitoringId,'ATTACHMENT_REMOVED',null,(int)($user['id']??0),$this->repository->activeFollowersCount($monitoringId),'Anexo “'.(string)($attachment['original_name']??$attachmentId).'” removido da ação #'.(int)($attachment['action_id']??0).'.');
        }
        return $removed;
    }

    public function deleteConcludedLink(int $monitoringUserId, array $user): void
    {
        if (!Roles::hasFullAccess((string)($user['role']??''))) {
            throw new InvalidArgumentException('Somente Administração ou Gestão podem excluir um vínculo concluído.');
        }
        if ($monitoringUserId <= 0) throw new InvalidArgumentException('Vínculo inválido.');
        foreach ($this->repository->actionIdsForLink($monitoringUserId) as $actionId) {
            $this->attachmentService->removeAll((int)$actionId);
        }
        if (!$this->repository->deleteConcludedLink($monitoringUserId)) {
            throw new InvalidArgumentException('O vínculo não foi encontrado ou ainda está ativo.');
        }
    }


    private function validateActionData(array $data): array
    {
        $date=trim((string)($data['action_date']??date('Y-m-d')));
        if(!$this->validDate($date)) throw new InvalidArgumentException('Informe uma data válida para a ação.');
        if($date>date('Y-m-d')) throw new InvalidArgumentException('A data da ação não pode estar no futuro.');
        $type=trim((string)($data['action_type']??''));
        $typeLabel=self::ACTION_TYPE_OPTIONS[$type]??'';
        if($typeLabel==='') throw new InvalidArgumentException('Selecione o tipo da ação.');
        $description=trim((string)($data['description']??''));
        if($description==='') throw new InvalidArgumentException('Descreva a ação realizada.');
        if(mb_strlen($description)>5000) throw new InvalidArgumentException('A descrição da ação está muito longa.');
        $issueType=trim((string)($data['issue_type']??'GENERAL'));
        if(!isset(self::ISSUE_TYPE_OPTIONS[$issueType])) throw new InvalidArgumentException('Selecione o problema relacionado à ação.');
        // A situação do caso é calculada pelos registros oficiais posteriores à intervenção.
        // O campo permanece no banco apenas para compatibilidade com ações antigas.
        $next=trim((string)($data['next_action']??''));
        return ['action_date'=>$date,'action_type'=>$type,'action_type_label'=>$typeLabel,'description'=>$description,'result_status'=>null,'issue_type'=>$issueType,'treatment_status'=>null,'next_action'=>$next!==''?$next:null];
    }

    private function assertCanManageAction(array $action, array $user): void
    {
        $userId=(int)($user['id']??0);
        $isManagement=Roles::hasFullAccess((string)($user['role']??''));
        if(!$isManagement&&(int)($action['created_by']??0)!==$userId){
            throw new InvalidArgumentException('Você não possui permissão para alterar esta ação.');
        }
    }

}
