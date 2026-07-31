<?php

declare(strict_types=1);

namespace App\Services\Intelligence;

use App\Repositories\Intelligence\IntelligenceRepository;
use App\Core\Settings\SettingManager;

class IntelligenceService
{
    public function __construct(private IntelligenceRepository $repository, private SettingManager $settings, private InsightEngine $insightEngine, private IntelligenceTimeline $timeline, private TrendAnalysisService $trendAnalysis, private PredictiveAnalysisService $predictiveAnalysis, private ClassTrendAnalysisService $classTrendAnalysis, private ClassPredictiveAnalysisService $classPredictiveAnalysis)
    {
    }

    public function dashboard(): array
    {
        $window = max(1, (int) $this->settings->get('intelligence.analysis_window_days', 30));
        $currentEnd = date('Y-m-d');
        $currentStart = date('Y-m-d', strtotime('-' . ($window - 1) . ' days'));
        $previousEnd = date('Y-m-d', strtotime('-' . $window . ' days'));
        $previousStart = date('Y-m-d', strtotime('-' . (($window * 2) - 1) . ' days'));

        $attendanceCurrent = $this->repository->attendanceTotals($currentStart, $currentEnd);
        $attendancePrevious = $this->repository->attendanceTotals($previousStart, $previousEnd);
        $occurrenceCurrent = $this->repository->occurrenceTotals($currentStart, $currentEnd);
        $occurrencePrevious = $this->repository->occurrenceTotals($previousStart, $previousEnd);

        $attendanceStudents = $this->repository->attendanceRiskStudents($currentStart, $currentEnd, 500);
        $occurrenceStudents = $this->repository->occurrenceRiskStudents($currentStart, $currentEnd, 500);
        $students = $this->mergeStudentSignals($attendanceStudents, $occurrenceStudents);
        $classSignals = $this->repository->classSignals($currentStart, $currentEnd, 200);
        $allClassSignals = $this->repository->classComparisonSignals($currentStart, $currentEnd);
        $predictiveClasses = array_map([$this, 'decorateClass'], $allClassSignals);
        $classIds = array_column($predictiveClasses, 'id');
        $classTrends = $this->classTrendAnalysis->classes($classIds, 30);
        $classPredictions = $this->classPredictiveAnalysis->classes($classIds, 30, 14);
        foreach ($predictiveClasses as &$class) {
            $classId = (int) ($class['id'] ?? 0);
            $class['historical_trends'] = $classTrends[$classId] ?? [];
            $class['prediction'] = $classPredictions[$classId] ?? [];
        }
        unset($class);
        $classes = array_values(array_filter(
            $predictiveClasses,
            static fn(array $class): bool => ($class['risk_level'] ?? 'LOW') !== 'LOW'
        ));
        $improvingClasses = array_values(array_filter($predictiveClasses, static fn(array $class): bool => (($class['historical_trends']['overall_status'] ?? '') === 'IMPROVING')));
        usort($improvingClasses, static fn(array $a, array $b): int => ((float) ($b['historical_trends']['evolution_score'] ?? 0)) <=> ((float) ($a['historical_trends']['evolution_score'] ?? 0)));

        $staleDays = max(1, (int) $this->settings->get('intelligence.stale_critical_days', 7));
        $staleCritical = $this->repository->staleCriticalOccurrences($staleDays);

        $attendanceTrend = $this->trend(
            (int) ($attendanceCurrent['unjustified_absences'] ?? 0),
            (int) ($attendancePrevious['unjustified_absences'] ?? 0),
            true
        );
        $occurrenceTrend = $this->trend(
            (int) ($occurrenceCurrent['total_occurrences'] ?? 0),
            (int) ($occurrencePrevious['total_occurrences'] ?? 0),
            true
        );

        $priorityStudents = array_values(array_filter($students, static fn(array $s): bool => $s['risk_level'] !== 'LOW'));
        $monitoring = $this->repository->monitoringOperationalSummary(array_column($priorityStudents, 'id'), 14);
        $monitoringByStudent = (array) ($monitoring['by_student'] ?? []);
        foreach ($priorityStudents as &$student) {
            $status = $monitoringByStudent[(int) ($student['id'] ?? 0)] ?? [];
            $student['monitoring'] = $status;
        }
        unset($student);

        $studentIds = array_column($priorityStudents, 'id');
        $studentTrends = $this->trendAnalysis->students($studentIds, 30);
        $studentPredictions = $this->predictiveAnalysis->students($studentIds, 30, 14);
        foreach ($priorityStudents as &$student) {
            $studentId = (int) ($student['id'] ?? 0);
            $student['historical_trends'] = $studentTrends[$studentId] ?? [];
            $student['prediction'] = $studentPredictions[$studentId] ?? [];
        }
        unset($student);

        $dashboard = [
            'period' => ['start' => $currentStart, 'end' => $currentEnd, 'days' => $window],
            'summary' => [
                'students_in_attention' => count(array_filter($students, static fn(array $s): bool => $s['risk_level'] !== 'LOW')),
                'critical_students' => count(array_filter($students, static fn(array $s): bool => $s['risk_level'] === 'CRITICAL')),
                'classes_in_attention' => count($classes),
                'combined_risk' => count(array_filter($students, static fn(array $s): bool => $s['unjustified_absences'] > 0 && $s['total_occurrences'] > 0)),
                'stale_critical_occurrences' => $staleCritical,
            ],
            'attendance' => [
                'unjustified_absences' => (int) ($attendanceCurrent['unjustified_absences'] ?? 0),
                'students_affected' => (int) ($attendanceCurrent['students_with_unjustified_absence'] ?? 0),
                'trend' => $attendanceTrend,
            ],
            'occurrences' => [
                'total' => (int) ($occurrenceCurrent['total_occurrences'] ?? 0),
                'serious' => (int) ($occurrenceCurrent['serious_occurrences'] ?? 0),
                'open' => (int) ($occurrenceCurrent['open_occurrences'] ?? 0),
                'trend' => $occurrenceTrend,
            ],
            'students' => array_slice($priorityStudents, 0, 5),
            'classes' => array_slice($classes, 0, 3),
            'predictive_classes' => $predictiveClasses,
            'improving_classes' => array_slice($improvingClasses, 0, 3),
            'monitoring' => $monitoring,
        ];
        $dashboard['insights'] = $this->insightEngine->school($dashboard);

        return $dashboard;
    }

    public function cases(string $type, array $filters = []): array
    {
        $allowed = ['low_attendance', 'students_attention', 'unjustified_absences', 'occurrences_period', 'serious_occurrences', 'critical_students', 'combined_risk', 'attention_classes', 'stale_critical_occurrences', 'stale_monitoring_actions'];
        if (!in_array($type, $allowed, true)) {
            $type = 'students_attention';
        }

        $window = max(1, (int) $this->settings->get('intelligence.analysis_window_days', 30));
        $end = date('Y-m-d');
        $start = date('Y-m-d', strtotime('-' . ($window - 1) . ' days'));
        $search = mb_strtolower(trim((string) ($filters['q'] ?? '')));
        $riskFilter = strtoupper(trim((string) ($filters['risk'] ?? '')));
        $classFilter = trim((string) ($filters['class'] ?? ''));

        $items = [];
        $kind = 'students';

        if ($type === 'attention_classes') {
            $kind = 'classes';
            $items = array_map([$this, 'decorateClass'], $this->repository->classSignals($start, $end, 200));
        } elseif ($type === 'stale_critical_occurrences') {
            $kind = 'occurrences';
            $staleDays = max(1, (int) $this->settings->get('intelligence.stale_critical_days', 7));
            $items = $this->repository->staleCriticalOccurrenceCases($staleDays);
        } elseif ($type === 'low_attendance') {
            $items = array_map(function (array $student): array {
                $percentage = (float) ($student['attendance_percentage'] ?? 0);
                $student['risk_score'] = max(0, min(100, (int) round(100 - $percentage)));
                $student['risk_level'] = $percentage < 70 ? 'CRITICAL' : ($percentage < 80 ? 'HIGH' : 'MODERATE');
                return $student;
            }, $this->repository->lowAttendanceStudents((float)$this->settings->get('school_goals.frequency_goal', 95.0)));

            $monitoringByStudent = $this->repository->activeMonitoringSummaries(array_column($items, 'id'));
            foreach ($items as &$item) {
                $studentId = (int) ($item['id'] ?? 0);
                $monitoring = $monitoringByStudent[$studentId] ?? [];
                $item['active_followers'] = (int) ($monitoring['active_followers'] ?? 0);
                $item['follower_names'] = (string) ($monitoring['follower_names'] ?? '');
            }
            unset($item);
        } else {
            $students = $this->mergeStudentSignals(
                $this->repository->attendanceRiskStudents($start, $end, 500),
                $this->repository->occurrenceRiskStudents($start, $end, 500)
            );
            $priorityStudents = array_values(array_filter($students, static fn(array $s): bool => $s['risk_level'] !== 'LOW'));
            $items = match ($type) {
                'unjustified_absences' => array_values(array_filter($students, static fn(array $s): bool => (int) ($s['unjustified_absences'] ?? 0) > 0)),
                'occurrences_period' => array_values(array_filter($students, static fn(array $s): bool => (int) ($s['total_occurrences'] ?? 0) > 0)),
                'serious_occurrences' => array_values(array_filter($students, static fn(array $s): bool => (int) ($s['serious_occurrences'] ?? 0) > 0)),
                'critical_students' => array_values(array_filter($students, static fn(array $s): bool => $s['risk_level'] === 'CRITICAL')),
                'combined_risk' => array_values(array_filter($students, static fn(array $s): bool => $s['unjustified_absences'] > 0 && $s['total_occurrences'] > 0)),
                'stale_monitoring_actions', 'without_monitoring' => $priorityStudents,
                default => $priorityStudents,
            };

            $monitoringByStudent = $this->repository->activeMonitoringSummaries(array_column($items, 'id'));
            $operationalByStudent = [];
            if (in_array($type, ['stale_monitoring_actions', 'without_monitoring'], true)) {
                $operational = $this->repository->monitoringOperationalSummary(array_column($priorityStudents, 'id'), 14);
                $operationalByStudent = (array) ($operational['by_student'] ?? []);
            }

            foreach ($items as &$item) {
                $studentId = (int) ($item['id'] ?? 0);
                $monitoring = $monitoringByStudent[$studentId] ?? [];
                $operational = $operationalByStudent[$studentId] ?? [];
                $item['monitoring_id'] = (int) ($operational['monitoring_id'] ?? $monitoring['monitoring_id'] ?? 0);
                $item['active_followers'] = (int) ($operational['active_followers'] ?? $monitoring['active_followers'] ?? 0);
                $item['follower_names'] = (string) ($monitoring['follower_names'] ?? '');
                $item['has_active_monitoring'] = (bool) ($operational['has_active_monitoring'] ?? ($item['active_followers'] > 0));
                $item['is_covered'] = (bool) ($operational['is_covered'] ?? ($item['active_followers'] > 0));
                $item['latest_action_date'] = $operational['latest_action_date'] ?? null;
                $item['monitoring_start_date'] = $operational['monitoring_start_date'] ?? null;
                $item['days_without_action'] = $operational['days_without_action'] ?? null;
                $item['without_recent_action'] = (bool) ($operational['without_recent_action'] ?? false);
            }
            unset($item);

            if ($type === 'stale_monitoring_actions') {
                $items = array_values(array_filter($items, static fn(array $item): bool => (bool) ($item['without_recent_action'] ?? false)));
            } elseif ($type === 'without_monitoring') {
                $items = array_values(array_filter($items, static fn(array $item): bool => !(bool) ($item['is_covered'] ?? false)));
            }
        }


        // Associa cada alerta ao caso correto. O aluno pode possuir vários casos ativos;
        // apenas o caso com o mesmo problema (ou explicitamente vinculado ao alerta)
        // é apresentado como destino do alerta.
        if ($kind === 'students' && $items !== []) {
            $studentIds=array_column($items,'id');
            $caseGroups=$this->repository->activeMonitoringCases($studentIds);
            $alert=$this->alertDefinition($type);
            $explicitLinks=$this->repository->linkedCasesForAlert($studentIds,(string)$alert['key']);
            foreach($items as &$item){
                $studentId=(int)($item['id']??0);
                $cases=(array)($caseGroups[$studentId]??[]);
                $matched=null;
                $previousMonitoringId=(int)($item['monitoring_id']??0);
                if($type==='stale_monitoring_actions'&&$previousMonitoringId>0){foreach($cases as $case){if((int)($case['monitoring_id']??0)===$previousMonitoringId){$matched=$case;break;}}}
                $linkedId=(int)($explicitLinks[$studentId]??0);
                if($matched===null&&$linkedId>0){foreach($cases as $case){if((int)($case['monitoring_id']??0)===$linkedId){$matched=$case;break;}}}
                if($matched===null){foreach($cases as $case){if((string)($case['problem_code']??'')===(string)$alert['problem_code']){$matched=$case;break;}}}
                $item['active_case_count']=count($cases);
                $item['related_cases']=$cases;
                $item['alert_key']=$alert['key'];
                $item['alert_type']=$type;
                $item['suggested_problem_code']=$alert['problem_code'];
                $item['suggested_case_title']=$alert['case_title'];
                $item['suggested_objective_code']=$alert['objective_code'];
                $item['suggested_strategies']=$alert['strategies'];
                $item['monitoring_id']=(int)($matched['monitoring_id']??0);
                $item['has_related_case']=$matched!==null;
                $item['active_followers']=(int)($matched['active_followers']??0);
                $item['follower_names']=(string)($matched['follower_names']??'');
                $item['latest_action_date']=$matched['latest_action_date']??($item['latest_action_date']??null);
                $item['monitoring_start_date']=$matched['start_date']??($item['monitoring_start_date']??null);
                $item['related_case_title']=(string)($matched['case_title']??'');
            }
            unset($item);
        }

        $items = array_values(array_filter($items, static function (array $item) use ($search, $riskFilter, $classFilter, $kind): bool {
            $flatten = static function (mixed $value) use (&$flatten): array {
                if (is_array($value)) {
                    $parts = [];
                    foreach ($value as $nestedValue) {
                        $parts = array_merge($parts, $flatten($nestedValue));
                    }
                    return $parts;
                }

                if ($value === null || is_bool($value)) {
                    return [];
                }

                if (is_scalar($value)) {
                    return [(string) $value];
                }

                return [];
            };

            $haystack = mb_strtolower(implode(' ', $flatten($item)));
            if ($search !== '' && !str_contains($haystack, $search)) {
                return false;
            }
            if ($riskFilter !== '' && $kind !== 'occurrences' && strtoupper((string) ($item['risk_level'] ?? '')) !== $riskFilter) {
                return false;
            }
            $itemClass = (string) ($item['class_name'] ?? $item['name'] ?? '');
            if ($classFilter !== '' && $itemClass !== $classFilter) {
                return false;
            }
            return true;
        }));

        $page = max(1, (int) ($filters['page'] ?? 1));
        $perPage = max(5, min(50, (int) ($filters['per_page'] ?? 10)));
        $total = count($items);
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $pages);
        $pagedItems = array_slice($items, ($page - 1) * $perPage, $perPage);

        $meta = $this->caseMeta($type);
        $summary = $this->caseSummary($items, $kind, $type);
        $classes = array_values(array_unique(array_filter(array_map(
            static fn(array $item): string => (string) ($item['class_name'] ?? ($kind === 'classes' ? $item['name'] ?? '' : '')),
            $items
        ))));
        sort($classes);

        return [
            'type' => $type,
            'kind' => $kind,
            'meta' => $meta,
            'period' => ['start' => $start, 'end' => $end, 'days' => $window],
            'summary' => $summary,
            'items' => $pagedItems,
            'classes' => $classes,
            'filters' => ['q' => (string) ($filters['q'] ?? ''), 'risk' => $riskFilter, 'class' => $classFilter, 'per_page' => $perPage],
            'pagination' => ['page' => $page, 'pages' => $pages, 'per_page' => $perPage, 'total' => $total],
        ];
    }


    public function casePrefillForAlert(string $type): array
    {
        $definition=$this->alertDefinition($type);
        return [
            'alert_type'=>$type,
            'alert_key'=>$definition['key'],
            'reason_code'=>$definition['problem_code'],
            'case_title'=>$definition['case_title'],
            'objective_code'=>$definition['objective_code'],
            'strategies'=>$definition['strategies'],
            'target_metric'=>$definition['target_metric'],
            'reason_details'=>$definition['details'],
        ];
    }

    public function linkAlertToCase(int $studentId,int $monitoringId,string $alertType,?int $userId): void
    {
        $definition=$this->alertDefinition($alertType);
        $this->repository->linkAlertToCase($studentId,$monitoringId,(string)$definition['key'],$alertType,$userId);
    }

    private function alertDefinition(string $type): array
    {
        return match($type){
            'low_attendance','unjustified_absences' => ['key'=>'ATTENDANCE_RISK','problem_code'=>'FREQUENCY','case_title'=>'Infrequência e faltas recorrentes','objective_code'=>'IMPROVE_ATTENDANCE','strategies'=>['STUDENT_CONVERSATION','FAMILY_CONTACT','WEEKLY_FOLLOWUP','ATTENDANCE_GOAL'],'target_metric'=>'ATTENDANCE','details'=>'Caso originado por alerta de frequência da Central de Inteligência.'],
            'occurrences_period','serious_occurrences','stale_critical_occurrences' => ['key'=>'OCCURRENCE_RISK','problem_code'=>'OCCURRENCES','case_title'=>'Ocorrências e comportamento','objective_code'=>'REDUCE_OCCURRENCES','strategies'=>['STUDENT_CONVERSATION','FAMILY_CONTACT','COORDINATION_REFERRAL'],'target_metric'=>'OCCURRENCES','details'=>'Caso originado por alerta de ocorrências da Central de Inteligência.'],
            'combined_risk','critical_students' => ['key'=>'COMBINED_RISK','problem_code'=>'OTHER','case_title'=>'Risco escolar combinado','objective_code'=>'OTHER','strategies'=>['STUDENT_CONVERSATION','FAMILY_CONTACT','WEEKLY_FOLLOWUP','COORDINATION_REFERRAL'],'target_metric'=>'NONE','details'=>'Caso originado por alerta de risco combinado da Central de Inteligência.'],
            'stale_monitoring_actions' => ['key'=>'STALE_MONITORING','problem_code'=>'OTHER','case_title'=>'Revisão de acompanhamento sem atualização','objective_code'=>'MAINTAIN_EVOLUTION','strategies'=>['WEEKLY_FOLLOWUP'],'target_metric'=>'NONE','details'=>'Revisão sugerida pela Central de Inteligência por ausência de atualização recente.'],
            default => ['key'=>'STUDENT_ATTENTION','problem_code'=>'OTHER','case_title'=>'Aluno em atenção','objective_code'=>'OTHER','strategies'=>['STUDENT_CONVERSATION','WEEKLY_FOLLOWUP'],'target_metric'=>'NONE','details'=>'Caso originado pela Central de Inteligência.'],
        };
    }

    /**
     * Produz sinais objetivos para um acompanhamento individual reutilizando
     * plano, métricas e ações já existentes. Não persiste alertas paralelos.
     */
    public function monitoringInsights(array $monitoring, ?array $plan, array $metrics, array $actions): array
    {
        $insights = [];
        $today = new \DateTimeImmutable('today');
        $startDate = !empty($monitoring['start_date']) ? new \DateTimeImmutable((string) $monitoring['start_date']) : $today;
        $endDate = !empty($monitoring['end_date']) ? new \DateTimeImmutable((string) $monitoring['end_date']) : $today;
        $daysActive = max(0, (int) $startDate->diff($today)->format('%r%a'));
        $daysToEnd = (int) $today->diff($endDate)->format('%r%a');

        $latestAction = $actions[0] ?? null;
        $daysWithoutAction = null;
        if ($latestAction && !empty($latestAction['action_date'])) {
            $lastDate = new \DateTimeImmutable((string) $latestAction['action_date']);
            $daysWithoutAction = max(0, (int) $lastDate->diff($today)->format('%r%a'));
        }

        if (!$latestAction && $daysActive >= 7) {
            $insights[] = $this->monitoringSignal(
                'NO_ACTIONS', 'HIGH', 'Acompanhamento sem ações registradas',
                'O acompanhamento está ativo há ' . $daysActive . ' dias e ainda não possui ações.',
                'Registre a primeira ação para iniciar o histórico de intervenção.', 'clipboard-plus'
            );
        } elseif ($daysWithoutAction !== null && $daysWithoutAction >= 14) {
            $insights[] = $this->monitoringSignal(
                'STALE', 'HIGH', 'Acompanhamento sem movimentação',
                'A última ação foi registrada há ' . $daysWithoutAction . ' dias.',
                'Revise o caso e registre uma nova providência somente se houver mudança de estratégia.', 'clock-alert'
            );
        } elseif ($daysWithoutAction !== null && $daysWithoutAction >= 7) {
            $insights[] = $this->monitoringSignal(
                'FOLLOW_UP_DUE', 'ATTENTION', 'Momento de revisar o acompanhamento',
                'Já se passaram ' . $daysWithoutAction . ' dias desde a última ação.',
                'Verifique a evolução do aluno e atualize o acompanhamento.', 'calendar-clock'
            );
        }

        if ($latestAction && trim((string) ($latestAction['next_action'] ?? '')) !== '' && ($daysWithoutAction ?? 0) >= 5) {
            $insights[] = $this->monitoringSignal(
                'NEXT_ACTION', 'ATTENTION', 'Próxima ação pendente',
                (string) $latestAction['next_action'],
                'Confirme se a ação planejada já foi realizada.', 'list-checks'
            );
        }

        $unjustified=(int)($metrics['total_faltas']??0);
        $justified=(int)($metrics['justified_absences']??0);
        $openOccurrences=(int)($metrics['open_occurrences']??0);
        $resolvedOccurrences=(int)($metrics['resolved_occurrences']??0);
        $absenceActions=array_values(array_filter($actions,static fn(array $a):bool=>($a['issue_type']??'')==='UNJUSTIFIED_ABSENCES'));
        $occurrenceActions=array_values(array_filter($actions,static fn(array $a):bool=>($a['issue_type']??'')==='OCCURRENCE'));
        $latestAbsenceAction=$absenceActions[0]??null;
        if($unjustified>0){
            if(!$latestAbsenceAction){
                $insights[]=$this->monitoringSignal('ABSENCE_INVESTIGATION','ATTENTION','Intervenção necessária para as faltas',$unjustified.' falta(s) sem justificativa foram registradas no período.','Registre uma conversa, contato com responsáveis ou outra providência vinculada às faltas.','message-circle-question');
            }else{
                $insights[]=$this->monitoringSignal('ABSENCE_TREATED','INFO','Faltas com intervenção registrada','A ação “'.(string)($latestAbsenceAction['action_type_label']??'Ação registrada').'” foi vinculada às faltas sem justificativa.','A evolução será verificada automaticamente pelos registros posteriores.','clipboard-check');
            }
        }
        if($justified>0){
            $insights[]=$this->monitoringSignal('JUSTIFIED_ABSENCES','POSITIVE','Pendências de frequência regularizadas',$justified.' falta(s) possuem justificativa registrada no período.','Considere o dado na leitura da evolução sem apagar o histórico de frequência.','file-check-2');
        }
        if($resolvedOccurrences>0){
            $insights[]=$this->monitoringSignal('OCCURRENCES_RESOLVED','POSITIVE','Ocorrências resolvidas',$resolvedOccurrences.' ocorrência(s) do período foram registradas como resolvidas.','Reconheça as estratégias que contribuíram para a resolução e acompanhe reincidências.','shield-check');
        }
        if($openOccurrences>0&&!$occurrenceActions){
            $insights[]=$this->monitoringSignal('OPEN_OCCURRENCE_ACTION','ATTENTION','Ocorrências precisam de intervenção','Há '.$openOccurrences.' ocorrência(s) ainda aberta(s) sem ação de acompanhamento relacionada.','Registre a providência adotada e atualize a situação do tratamento.','clipboard-list');
        }elseif($openOccurrences===0&&$resolvedOccurrences>0){
            $insights[]=$this->monitoringSignal('NO_OPEN_OCCURRENCES','POSITIVE','Sem ocorrências pendentes','As ocorrências registradas no período estão resolvidas.','Mantenha o acompanhamento e observe se não há reincidência.','sparkles');
        }

        $comparison=$metrics['intervention_comparison']??null;
        if(is_array($comparison)){
            $before=(array)($comparison['before']??[]);$after=(array)($comparison['after']??[]);
            $beforeRecords=(int)($before['total_records']??0);$afterRecords=(int)($after['total_records']??0);
            if($beforeRecords>0&&$afterRecords>0){
                $beforeFrequency=(float)($before['attendance_percentage']??0);$afterFrequency=(float)($after['attendance_percentage']??0);
                $difference=round($afterFrequency-$beforeFrequency,1);
                if($difference>=3){
                    $insights[]=$this->monitoringSignal('POST_ACTION_ATTENDANCE','POSITIVE','Frequência melhorou após a intervenção','A frequência passou de '.number_format($beforeFrequency,1,',','.').'% para '.number_format($afterFrequency,1,',','.').'% após “'.(string)($comparison['action_type_label']??'ação registrada').'”.','Mantenha as estratégias associadas à melhora e continue observando os registros.','chart-no-axes-combined');
                }elseif($difference<=-3){
                    $insights[]=$this->monitoringSignal('POST_ACTION_ATTENDANCE_DROP','ATTENTION','Frequência ainda não respondeu à intervenção','Após a ação, a frequência variou de '.number_format($beforeFrequency,1,',','.').'% para '.number_format($afterFrequency,1,',','.').'%.','Revise a estratégia e registre uma nova providência orientada ao motivo identificado.','chart-no-axes-combined');
                }
            }
            $beforeOccurrences=(int)($before['total_occurrences']??0);$afterOccurrences=(int)($after['total_occurrences']??0);
            if($beforeOccurrences>0&&$afterOccurrences===0){
                $insights[]=$this->monitoringSignal('POST_ACTION_NO_OCCURRENCES','POSITIVE','Sem novas ocorrências após a intervenção','Não foram registradas novas ocorrências após “'.(string)($comparison['action_type_label']??'ação registrada').'”.','Continue observando para confirmar a manutenção sem reincidência.','shield-check');
            }
        }

        $targetMetric = (string) ($plan['target_metric'] ?? 'NONE');
        $targetValue = isset($plan['target_value']) && $plan['target_value'] !== null ? (float) $plan['target_value'] : null;
        $baseline = isset($plan['baseline_value']) && $plan['baseline_value'] !== null ? (float) $plan['baseline_value'] : null;

        if ($targetMetric === 'ATTENDANCE' && $targetValue !== null && (int) ($metrics['total_records'] ?? 0) > 0) {
            $current = (float) ($metrics['attendance_percentage'] ?? 0);
            if ($current >= $targetValue) {
                $insights[] = $this->monitoringSignal(
                    'TARGET_REACHED', 'POSITIVE', 'Meta de frequência atingida',
                    'Frequência atual de ' . number_format($current, 1, ',', '.') . '% para uma meta de ' . number_format($targetValue, 1, ',', '.') . '%.',
                    'Avalie a manutenção da evolução com os próximos registros.', 'badge-check'
                );
            } elseif ($baseline !== null && $current > $baseline) {
                $gain = $current - $baseline;
                $insights[] = $this->monitoringSignal(
                    'PROGRESS', 'POSITIVE', 'Frequência em evolução',
                    'A frequência aumentou ' . number_format($gain, 1, ',', '.') . ' ponto(s) percentual(is) desde o início.',
                    'Mantenha as estratégias que estão produzindo resultado.', 'trending-up'
                );
            } elseif ($baseline !== null && $current < $baseline) {
                $drop = $baseline - $current;
                $insights[] = $this->monitoringSignal(
                    'REGRESSION', 'HIGH', 'Frequência abaixo do ponto inicial',
                    'A frequência caiu ' . number_format($drop, 1, ',', '.') . ' ponto(s) percentual(is) desde o início.',
                    'Revise as estratégias e considere contato com o aluno ou responsáveis.', 'trending-down'
                );
            } else {
                $remaining = max(0, $targetValue - $current);
                $insights[] = $this->monitoringSignal(
                    'TARGET_DISTANCE', 'INFO', 'Meta de frequência em andamento',
                    'Faltam ' . number_format($remaining, 1, ',', '.') . ' ponto(s) percentual(is) para a meta.',
                    'Continue registrando ações para relacionar as intervenções à evolução.', 'target'
                );
            }
        }

        if ($targetMetric === 'OCCURRENCES' && $targetValue !== null) {
            $current = (float) ($metrics['total_occurrences'] ?? 0);
            if ($current <= $targetValue) {
                $insights[] = $this->monitoringSignal(
                    'TARGET_REACHED', 'POSITIVE', 'Meta de ocorrências dentro do esperado',
                    'Foram registradas ' . (int) $current . ' ocorrência(s), dentro do limite de ' . (int) $targetValue . '.',
                    'Continue acompanhando para manter o resultado.', 'badge-check'
                );
            } else {
                $excess = (int) ($current - $targetValue);
                $insights[] = $this->monitoringSignal(
                    'TARGET_EXCEEDED', 'HIGH', 'Meta de ocorrências ultrapassada',
                    'O limite foi excedido em ' . $excess . ' ocorrência(s).',
                    'Revise o plano e registre uma ação direcionada ao comportamento observado.', 'triangle-alert'
                );
            }
        }

        if ($daysToEnd >= 0 && $daysToEnd <= 7) {
            $insights[] = $this->monitoringSignal(
                'ENDING_SOON', 'ATTENTION', 'Período próximo do encerramento',
                'O período atual termina em ' . $daysToEnd . ' dia(s).',
                'Avalie os resultados para decidir entre encerrar ou prorrogar o acompanhamento.', 'calendar-range'
            );
        }

        $weight = ['HIGH' => 4, 'ATTENTION' => 3, 'INFO' => 2, 'POSITIVE' => 1];
        usort($insights, static fn(array $a, array $b): int => ($weight[$b['level']] ?? 0) <=> ($weight[$a['level']] ?? 0));
        return array_slice($insights, 0, 4);
    }

    private function monitoringSignal(string $code, string $level, string $title, string $message, string $recommendation, string $icon): array
    {
        return compact('code', 'level', 'title', 'message', 'recommendation', 'icon');
    }

    private function caseMeta(string $type): array
    {
        return match ($type) {
            'low_attendance' => ['title' => 'Alunos com frequência abaixo da meta', 'subtitle' => 'Alunos ativos cuja frequência acumulada está abaixo da meta institucional configurada.', 'icon' => 'triangle-alert', 'tone' => 'danger'],
            'unjustified_absences' => ['title' => 'Alunos com faltas sem justificativa', 'subtitle' => 'Alunos que receberam ao menos uma falta F no período analisado.', 'icon' => 'user-x', 'tone' => 'danger'],
            'occurrences_period' => ['title' => 'Alunos com ocorrências no período', 'subtitle' => 'Alunos da turma que possuem ao menos uma ocorrência no período analisado.', 'icon' => 'clipboard-alert', 'tone' => 'warning'],
            'serious_occurrences' => ['title' => 'Alunos com ocorrências graves', 'subtitle' => 'Alunos da turma com ocorrências de gravidade alta ou crítica no período analisado.', 'icon' => 'shield-alert', 'tone' => 'danger'],
            'critical_students' => ['title' => 'Alunos em risco crítico', 'subtitle' => 'Casos que atingiram o nível crítico no período analisado.', 'icon' => 'siren', 'tone' => 'danger'],
            'combined_risk' => ['title' => 'Alunos com risco combinado', 'subtitle' => 'Alunos com faltas sem justificativa e ocorrências no mesmo período.', 'icon' => 'git-merge', 'tone' => 'primary'],
            'attention_classes' => ['title' => 'Turmas em atenção', 'subtitle' => 'Turmas que concentram sinais de frequência e ocorrências.', 'icon' => 'school', 'tone' => 'info'],
            'stale_critical_occurrences' => ['title' => 'Ocorrências críticas atrasadas', 'subtitle' => 'Ocorrências críticas abertas além do prazo configurado.', 'icon' => 'clock-alert', 'tone' => 'danger'],
            'without_monitoring' => ['title' => 'Alunos prioritários sem acompanhamento', 'subtitle' => 'Alunos classificados como prioritários que não possuem nenhum participante ativo em seus casos.', 'icon' => 'user-minus', 'tone' => 'danger'],
            'stale_monitoring_actions' => ['title' => 'Alunos sem atualização de intervenção', 'subtitle' => 'Casos prioritários com acompanhamento ativo cuja última intervenção ocorreu há 14 dias ou mais; quando não há intervenção, o prazo é contado desde o início do acompanhamento.', 'icon' => 'history', 'tone' => 'warning'],
            default => ['title' => 'Alunos em atenção', 'subtitle' => 'Alunos classificados nos níveis Atenção, Alto ou Crítico.', 'icon' => 'user-round-search', 'tone' => 'warning'],
        };
    }

    private function caseSummary(array $items, string $kind, string $type = ''): array
    {
        if ($kind === 'occurrences') {
            return [
                ['label' => 'Total de casos', 'value' => count($items), 'icon' => 'files'],
                ['label' => 'Maior atraso', 'value' => $items === [] ? 0 : max(array_map(static fn(array $i): int => (int) ($i['days_open'] ?? 0), $items)), 'suffix' => ' dias', 'icon' => 'clock-3'],
                ['label' => 'Alunos envolvidos', 'value' => count(array_unique(array_column($items, 'student_id'))), 'icon' => 'users-round'],
            ];
        }

        if ($kind === 'students' && $type === 'stale_monitoring_actions') {
            $withoutAnyAction = count(array_filter($items, static fn(array $i): bool => empty($i['latest_action_date'])));
            $largestDelay = 0;
            foreach ($items as $item) {
                $largestDelay = max($largestDelay, (int) ($item['days_without_action'] ?? 0));
            }

            return [
                ['label' => 'Total de casos', 'value' => count($items), 'icon' => 'history'],
                ['label' => 'Sem ação registrada', 'value' => $withoutAnyAction, 'icon' => 'circle-off'],
                ['label' => 'Maior intervalo', 'value' => $largestDelay, 'suffix' => ' dias', 'icon' => 'clock-3'],
                ['label' => 'Com acompanhante', 'value' => count(array_filter($items, static fn(array $i): bool => (int) ($i['active_followers'] ?? 0) > 0)), 'icon' => 'user-round-check'],
            ];
        }

        if ($kind === 'students') {
            return [
                ['label' => 'Total de casos', 'value' => count($items), 'icon' => 'list-filter'],
                ['label' => 'Já acompanhados', 'value' => count(array_filter($items, static fn(array $i): bool => (int) ($i['active_followers'] ?? 0) > 0)), 'icon' => 'user-round-check'],
                ['label' => 'Acompanhantes ativos', 'value' => array_sum(array_map(static fn(array $i): int => (int) ($i['active_followers'] ?? 0), $items)), 'icon' => 'users-round'],
                ['label' => 'Críticos', 'value' => count(array_filter($items, static fn(array $i): bool => ($i['risk_level'] ?? '') === 'CRITICAL')), 'icon' => 'siren'],
            ];
        }

        return [
            ['label' => 'Total de casos', 'value' => count($items), 'icon' => 'list-filter'],
            ['label' => 'Críticos', 'value' => count(array_filter($items, static fn(array $i): bool => ($i['risk_level'] ?? '') === 'CRITICAL')), 'icon' => 'siren'],
            ['label' => 'Risco alto', 'value' => count(array_filter($items, static fn(array $i): bool => ($i['risk_level'] ?? '') === 'HIGH')), 'icon' => 'triangle-alert'],
            ['label' => 'Em atenção', 'value' => count(array_filter($items, static fn(array $i): bool => ($i['risk_level'] ?? '') === 'MODERATE')), 'icon' => 'eye'],
        ];
    }

    public function classComparisons(?int $highlightClassId = null): array
    {
        $window = max(1, (int) $this->settings->get('intelligence.analysis_window_days', 30));
        $currentEnd = date('Y-m-d');
        $currentStart = date('Y-m-d', strtotime('-' . ($window - 1) . ' days'));
        $previousEnd = date('Y-m-d', strtotime('-' . $window . ' days'));
        $previousStart = date('Y-m-d', strtotime('-' . (($window * 2) - 1) . ' days'));

        $currentRows = $this->repository->classComparisonSignals($currentStart, $currentEnd);
        $previousRows = $this->repository->classComparisonSignals($previousStart, $previousEnd);
        $previousById = [];
        foreach ($previousRows as $row) {
            $previousById[(int) $row['id']] = $row;
        }

        $classes = [];
        foreach ($currentRows as $row) {
            $id = (int) $row['id'];
            $previous = $previousById[$id] ?? [];
            $activeStudents = max(0, (int) ($row['active_students'] ?? 0));
            $totalRecords = max(0, (int) ($row['total_records'] ?? 0));
            $presences = max(0, (int) ($row['presences'] ?? 0));
            $frequency = $totalRecords > 0 ? round(($presences / $totalRecords) * 100, 1) : null;

            $decorated = $this->decorateClass([
                'id' => $id,
                'name' => (string) ($row['name'] ?? 'Turma'),
                'unjustified_absences' => (int) ($row['unjustified_absences'] ?? 0),
                'total_occurrences' => (int) ($row['total_occurrences'] ?? 0),
                'serious_occurrences' => (int) ($row['serious_occurrences'] ?? 0),
            ]);

            $classes[] = array_merge($row, [
                'active_students' => $activeStudents,
                'frequency_percentage' => $frequency,
                'risk_score' => (int) $decorated['risk_score'],
                'risk_level' => (string) $decorated['risk_level'],
                'absence_rate' => $activeStudents > 0 ? round(((int) ($row['unjustified_absences'] ?? 0)) / $activeStudents, 2) : 0.0,
                'occurrence_rate' => $activeStudents > 0 ? round(((int) ($row['total_occurrences'] ?? 0)) / $activeStudents, 2) : 0.0,
                'attendance_trend' => $this->trend((int) ($row['unjustified_absences'] ?? 0), (int) ($previous['unjustified_absences'] ?? 0), true),
                'occurrence_trend' => $this->trend((int) ($row['total_occurrences'] ?? 0), (int) ($previous['total_occurrences'] ?? 0), true),
                'highlighted' => $highlightClassId !== null && $highlightClassId === $id,
            ]);
        }

        usort($classes, static function (array $a, array $b): int {
            return [$b['risk_score'], $b['serious_occurrences'], $b['unjustified_absences'], $a['name']]
                <=> [$a['risk_score'], $a['serious_occurrences'], $a['unjustified_absences'], $b['name']];
        });
        foreach ($classes as $index => &$class) {
            $class['rank'] = $index + 1;
        }
        unset($class);

        $count = count($classes);
        $averageRisk = $count > 0 ? round(array_sum(array_column($classes, 'risk_score')) / $count, 1) : 0.0;
        $frequencyValues = array_values(array_filter(array_column($classes, 'frequency_percentage'), static fn($value): bool => $value !== null));
        $averageFrequency = $frequencyValues !== [] ? round(array_sum($frequencyValues) / count($frequencyValues), 1) : null;

        return [
            'period' => ['start' => $currentStart, 'end' => $currentEnd, 'days' => $window],
            'summary' => [
                'classes' => $count,
                'average_risk' => $averageRisk,
                'average_frequency' => $averageFrequency,
                'attention_classes' => count(array_filter($classes, static fn(array $class): bool => ($class['risk_level'] ?? 'LOW') !== 'LOW')),
            ],
            'classes' => $classes,
            'highlight_class_id' => $highlightClassId,
        ];
    }

    public function studentDashboard(int $studentId, array $monitoringContext = []): array
    {
        $window = max(1, (int) $this->settings->get('intelligence.analysis_window_days', 30));
        $currentEnd = date('Y-m-d');
        $currentStart = date('Y-m-d', strtotime('-' . ($window - 1) . ' days'));
        $previousEnd = date('Y-m-d', strtotime('-' . $window . ' days'));
        $previousStart = date('Y-m-d', strtotime('-' . (($window * 2) - 1) . ' days'));

        $attendance = $this->repository->studentAttendanceTotals($studentId, $currentStart, $currentEnd);
        $attendancePrevious = $this->repository->studentAttendanceTotals($studentId, $previousStart, $previousEnd);
        $occurrences = $this->repository->studentOccurrenceTotals($studentId, $currentStart, $currentEnd);
        $occurrencesPrevious = $this->repository->studentOccurrenceTotals($studentId, $previousStart, $previousEnd);

        $signal = [
            'unjustified_absences' => (int) ($attendance['unjustified_absences'] ?? 0),
            'attenuated_absences' => (int) ($attendance['attenuated_absences'] ?? 0),
            'total_occurrences' => (int) ($occurrences['total_occurrences'] ?? 0),
            'serious_occurrences' => (int) ($occurrences['serious_occurrences'] ?? 0),
            'open_occurrences' => (int) ($occurrences['open_occurrences'] ?? 0),
        ];

        $risk = $this->assessRisk($signal);
        $attendanceTrend = $this->trend(
            $signal['unjustified_absences'],
            (int) ($attendancePrevious['unjustified_absences'] ?? 0),
            true
        );
        $occurrenceTrend = $this->trend(
            $signal['total_occurrences'],
            (int) ($occurrencesPrevious['total_occurrences'] ?? 0),
            true
        );

        $recommendations = [];
        if ($risk['level'] === 'CRITICAL') {
            $recommendations[] = ['level' => 'CRITICAL', 'icon' => 'siren', 'title' => 'Intervenção prioritária', 'text' => (bool) $this->settings->get('intelligence.recommendation_guardian_contact', true)
                ? 'O conjunto de sinais indica necessidade de análise imediata pela gestão e contato com o responsável.'
                : 'O conjunto de sinais indica necessidade de análise imediata pela gestão e definição de intervenção pedagógica.'];
        } elseif ($risk['level'] === 'HIGH') {
            $recommendations[] = ['level' => 'HIGH', 'icon' => 'user-round-search', 'title' => 'Acompanhamento individual', 'text' => 'Recomenda-se acompanhamento pedagógico próximo e revisão dos registros recentes.'];
        } elseif ($risk['level'] === 'MODERATE') {
            $recommendations[] = ['level' => 'MODERATE', 'icon' => 'eye', 'title' => 'Monitoramento preventivo', 'text' => 'Há sinais que merecem acompanhamento antes que evoluam para uma situação de maior risco.'];
        }
        if ($signal['unjustified_absences'] > 0) {
            $recommendations[] = ['level' => 'HIGH', 'icon' => 'calendar-x', 'title' => 'Verificar faltas sem justificativa', 'text' => $signal['unjustified_absences'] . ' falta(s) sem justificativa foram registradas no período.'];
        }
        if ($signal['open_occurrences'] > 0) {
            $recommendations[] = ['level' => 'MODERATE', 'icon' => 'clipboard-check', 'title' => 'Revisar ocorrências abertas', 'text' => $signal['open_occurrences'] . ' ocorrência(s) ainda aguardam resolução ou providência.'];
        }
        $activeMonitoring = !empty($monitoringContext['monitoring']);
        $actionsTotal = (int) ($monitoringContext['actions_summary']['total'] ?? 0);
        $hasPlan = !empty($monitoringContext['plan']);

        if ($activeMonitoring) {
            // A recomendação genérica de iniciar acompanhamento deixa de fazer
            // sentido quando o aluno já possui acompanhamento ativo.
            $recommendations = array_values(array_filter(
                $recommendations,
                static fn (array $recommendation): bool => !in_array(
                    (string) ($recommendation['title'] ?? ''),
                    ['Acompanhamento individual', 'Monitoramento preventivo'],
                    true
                )
            ));

            if ($actionsTotal === 0) {
                $recommendations[] = [
                    'level' => 'MODERATE',
                    'icon' => 'clipboard-pen-line',
                    'title' => 'Registrar primeira ação',
                    'text' => $hasPlan
                        ? 'O acompanhamento e o plano já estão ativos. Registre a primeira ação realizada para iniciar o histórico de intervenções.'
                        : 'O acompanhamento já está ativo. Complete o plano e registre a primeira ação realizada.',
                ];
            }
        }

        if ($recommendations === []) {
            $recommendations[] = ['level' => 'LOW', 'icon' => 'badge-check', 'title' => 'Cenário estável', 'text' => 'Nenhum sinal prioritário foi identificado no período analisado.'];
        }

        $dashboard = [
            'period' => ['start' => $currentStart, 'end' => $currentEnd, 'days' => $window],
            'risk' => $risk,
            'attendance' => [
                'total_records' => (int) ($attendance['total_records'] ?? 0),
                'presences' => (int) ($attendance['presences'] ?? 0),
                'unjustified_absences' => $signal['unjustified_absences'],
                'attenuated_absences' => $signal['attenuated_absences'],
                'trend' => $attendanceTrend,
            ],
            'occurrences' => [
                'total' => $signal['total_occurrences'],
                'serious' => $signal['serious_occurrences'],
                'open' => $signal['open_occurrences'],
                'resolved' => (int) ($occurrences['resolved_occurrences'] ?? 0),
                'trend' => $occurrenceTrend,
            ],
            'reasons' => array_values(array_filter([
                $signal['unjustified_absences'] > 0 ? $signal['unjustified_absences'] . ' falta(s) sem justificativa' : null,
                $signal['total_occurrences'] > 0 ? $signal['total_occurrences'] . ' ocorrência(s) no período' : null,
                $signal['serious_occurrences'] > 0 ? $signal['serious_occurrences'] . ' ocorrência(s) de alta gravidade' : null,
                $signal['open_occurrences'] > 0 ? $signal['open_occurrences'] . ' ocorrência(s) aberta(s)' : null,
            ])),
            'recommendations' => array_slice($recommendations, 0, 4),
            'historical_trends' => $this->trendAnalysis->student($studentId, 30),
            'prediction' => $this->predictiveAnalysis->student($studentId, 30, 14),
        ];
        $dashboard['timeline'] = $this->timeline->student($dashboard, $studentId);

        return $dashboard;
    }

    public function classDashboard(int $classId): array
    {
        $window = max(1, (int) $this->settings->get('intelligence.analysis_window_days', 30));
        $currentEnd = date('Y-m-d');
        $currentStart = date('Y-m-d', strtotime('-' . ($window - 1) . ' days'));
        $previousEnd = date('Y-m-d', strtotime('-' . $window . ' days'));
        $previousStart = date('Y-m-d', strtotime('-' . (($window * 2) - 1) . ' days'));

        $attendance = $this->repository->classAttendanceTotals($classId, $currentStart, $currentEnd);
        $attendancePrevious = $this->repository->classAttendanceTotals($classId, $previousStart, $previousEnd);
        $occurrences = $this->repository->classOccurrenceTotals($classId, $currentStart, $currentEnd);
        $occurrencesPrevious = $this->repository->classOccurrenceTotals($classId, $previousStart, $previousEnd);

        $students = $this->mergeStudentSignals(
            $this->repository->classAttendanceRiskStudents($classId, $currentStart, $currentEnd),
            $this->repository->classOccurrenceRiskStudents($classId, $currentStart, $currentEnd)
        );

        $distribution = ['LOW' => 0, 'MODERATE' => 0, 'HIGH' => 0, 'CRITICAL' => 0];
        foreach ($students as $student) {
            $level = (string) ($student['risk_level'] ?? 'LOW');
            $distribution[$level] = ($distribution[$level] ?? 0) + 1;
        }

        $classSignal = [
            'unjustified_absences' => (int) ($attendance['unjustified_absences'] ?? 0),
            'total_occurrences' => (int) ($occurrences['total_occurrences'] ?? 0),
            'serious_occurrences' => (int) ($occurrences['serious_occurrences'] ?? 0),
        ];
        $risk = $this->decorateClass(array_merge(['id' => $classId, 'name' => 'Turma'], $classSignal));

        $attendanceTrend = $this->trend(
            (int) ($attendance['unjustified_absences'] ?? 0),
            (int) ($attendancePrevious['unjustified_absences'] ?? 0),
            true
        );
        $occurrenceTrend = $this->trend(
            (int) ($occurrences['total_occurrences'] ?? 0),
            (int) ($occurrencesPrevious['total_occurrences'] ?? 0),
            true
        );

        $recommendations = [];
        if (($distribution['CRITICAL'] ?? 0) > 0) {
            $recommendations[] = ['level' => 'CRITICAL', 'icon' => 'siren', 'title' => 'Intervenção imediata', 'text' => $distribution['CRITICAL'] . ' aluno(s) estão em risco crítico e devem receber acompanhamento individual.', 'explanation' => 'Esta recomendação aparece porque há aluno(s) classificados no nível crítico pelas regras de risco da Central de Inteligência. A prioridade é revisar esses casos individualmente e registrar a providência adotada.'];
        }
        if (($distribution['HIGH'] ?? 0) > 0) {
            $recommendations[] = ['level' => 'HIGH', 'icon' => 'user-round-search', 'title' => 'Acompanhar alunos de alto risco', 'text' => $distribution['HIGH'] . ' aluno(s) apresentam sinais elevados no período analisado.', 'explanation' => 'A sugestão é exibida porque há aluno(s) com pontuação de risco alta. O acompanhamento permite verificar quais registros de frequência e ocorrências sustentam essa classificação.'];
        }
        if ((int) ($attendance['unjustified_absences'] ?? 0) > 0) {
            $recommendations[] = ['level' => 'HIGH', 'icon' => 'calendar-x', 'title' => 'Revisar faltas sem justificativa', 'text' => (int) $attendance['unjustified_absences'] . ' falta(s) sem justificativa atingiram ' . (int) ($attendance['students_with_unjustified_absence'] ?? 0) . ' aluno(s).', 'explanation' => 'Esta recomendação considera somente registros com status F. Faltas justificadas, atestados médicos e faltas de ônibus não são tratadas como faltas sem justificativa neste indicador.'];
        }
        if ((int) ($occurrences['open_occurrences'] ?? 0) > 0) {
            $recommendations[] = ['level' => 'MODERATE', 'icon' => 'clipboard-check', 'title' => 'Resolver ocorrências abertas', 'text' => (int) $occurrences['open_occurrences'] . ' ocorrência(s) da turma ainda aguardam resolução.', 'explanation' => 'A recomendação é apresentada porque existem ocorrências sem resolução registrada. Revisá-las ajuda a manter o histórico atualizado e evita que pendências permaneçam sem providência.'];
        }
        if ($recommendations === []) {
            $recommendations[] = ['level' => 'LOW', 'icon' => 'badge-check', 'title' => 'Cenário estável', 'text' => 'Nenhum sinal prioritário foi identificado para a turma no período.', 'explanation' => 'O sistema não encontrou alunos em risco alto ou crítico, faltas sem justificativa ou ocorrências abertas suficientes para gerar uma ação prioritária nesta janela de análise.'];
        }

        $priorityStudents = array_values(array_filter($students, static fn(array $s): bool => $s['risk_level'] !== 'LOW'));
        $monitoring = $this->repository->monitoringOperationalSummary(array_column($priorityStudents, 'id'), 14);
        $monitoringByStudent = (array) ($monitoring['by_student'] ?? []);
        foreach ($priorityStudents as &$student) {
            $status = $monitoringByStudent[(int) ($student['id'] ?? 0)] ?? [];
            $student['monitoring'] = $status;
        }
        unset($student);

        $dashboard = [
            'period' => ['start' => $currentStart, 'end' => $currentEnd, 'days' => $window],
            'risk' => ['score' => (int) $risk['risk_score'], 'level' => (string) $risk['risk_level']],
            'distribution' => $distribution,
            'students' => $students,
            'priority_students' => array_slice(array_values(array_filter($students, static fn(array $student): bool => ($student['risk_level'] ?? 'LOW') !== 'LOW')), 0, 6),
            'attendance' => [
                'total_records' => (int) ($attendance['total_records'] ?? 0),
                'presences' => (int) ($attendance['presences'] ?? 0),
                'unjustified_absences' => (int) ($attendance['unjustified_absences'] ?? 0),
                'attenuated_absences' => (int) ($attendance['attenuated_absences'] ?? 0),
                'students_affected' => (int) ($attendance['students_with_unjustified_absence'] ?? 0),
                'trend' => $attendanceTrend,
            ],
            'occurrences' => [
                'total' => (int) ($occurrences['total_occurrences'] ?? 0),
                'serious' => (int) ($occurrences['serious_occurrences'] ?? 0),
                'open' => (int) ($occurrences['open_occurrences'] ?? 0),
                'resolved' => (int) ($occurrences['resolved_occurrences'] ?? 0),
                'students_affected' => (int) ($occurrences['students_with_occurrences'] ?? 0),
                'trend' => $occurrenceTrend,
            ],
            'recommendations' => array_slice($recommendations, 0, 4),
            'historical_trends' => $this->classTrendAnalysis->schoolClass($classId, 30),
            'prediction' => $this->classPredictiveAnalysis->schoolClass($classId, 30, 14),
        ];
        $dashboard['timeline'] = $this->timeline->class($dashboard, $classId);

        return $dashboard;
    }

    private function assessRisk(array $signal): array
    {
        $frequencyWeight = max(0, (int) $this->settings->get('intelligence.risk_weight_frequency', 40));
        $occurrenceWeight = max(0, (int) $this->settings->get('intelligence.risk_weight_occurrences', 35));
        $severityWeight = max(0, (int) $this->settings->get('intelligence.risk_weight_severity', 25));
        $moderate = (int) $this->settings->get('intelligence.risk_moderate_threshold', 20);
        $high = (int) $this->settings->get('intelligence.risk_high_threshold', 45);
        $critical = (int) $this->settings->get('intelligence.risk_critical_threshold', 70);

        $score = min($frequencyWeight, (int) ($signal['unjustified_absences'] ?? 0) * max(1, (int) ceil($frequencyWeight / 8)))
            + min($occurrenceWeight, (int) ($signal['total_occurrences'] ?? 0) * max(1, (int) ceil($occurrenceWeight / 5)))
            + min($severityWeight, (int) ($signal['serious_occurrences'] ?? 0) * max(1, (int) ceil($severityWeight / 2)));
        $score = min(100, $score);

        $level = match (true) {
            $score >= $critical => 'CRITICAL',
            $score >= $high => 'HIGH',
            $score >= $moderate => 'MODERATE',
            default => 'LOW',
        };

        return ['score' => $score, 'level' => $level];
    }

    private function mergeStudentSignals(array $attendance, array $occurrences): array
    {
        $students = [];
        foreach ($attendance as $row) {
            $id = (int) $row['id'];
            $students[$id] = [
                'id' => $id,
                'name' => (string) $row['name'],
                'registration' => (string) ($row['registration'] ?? ''),
                'class_name' => (string) ($row['class_name'] ?? 'Sem turma'),
                'unjustified_absences' => (int) $row['unjustified_absences'],
                'attenuated_absences' => (int) $row['attenuated_absences'],
                'total_occurrences' => 0,
                'serious_occurrences' => 0,
                'open_occurrences' => 0,
            ];
        }
        foreach ($occurrences as $row) {
            $id = (int) $row['id'];
            $students[$id] ??= [
                'id' => $id,
                'name' => (string) $row['name'],
                'registration' => (string) ($row['registration'] ?? ''),
                'class_name' => (string) ($row['class_name'] ?? 'Sem turma'),
                'unjustified_absences' => 0,
                'attenuated_absences' => 0,
                'total_occurrences' => 0,
                'serious_occurrences' => 0,
                'open_occurrences' => 0,
            ];
            $students[$id]['total_occurrences'] = (int) $row['total_occurrences'];
            $students[$id]['serious_occurrences'] = (int) $row['serious_occurrences'];
            $students[$id]['open_occurrences'] = (int) $row['open_occurrences'];
        }
        foreach ($students as &$student) {
            $risk = $this->assessRisk($student);
            $student['risk_score'] = $risk['score'];
            $student['risk_level'] = $risk['level'];
            $student['reasons'] = array_values(array_filter([
                $student['unjustified_absences'] > 0 ? $student['unjustified_absences'] . ' falta(s) sem justificativa' : null,
                $student['total_occurrences'] > 0 ? $student['total_occurrences'] . ' ocorrência(s)' : null,
                $student['serious_occurrences'] > 0 ? $student['serious_occurrences'] . ' de alta gravidade' : null,
            ]));
        }
        unset($student);
        usort($students, static fn(array $a, array $b): int => $b['risk_score'] <=> $a['risk_score']);
        return array_values($students);
    }

    private function trend(int $current, int $previous, bool $lowerIsBetter): array
    {
        if ($previous === 0) {
            $percentage = $current === 0 ? 0.0 : 100.0;
        } else {
            $percentage = (($current - $previous) / $previous) * 100;
        }
        $improved = $lowerIsBetter ? $percentage < 0 : $percentage > 0;
        $stableMargin = max(0, (float) $this->settings->get('intelligence.trend_stable_margin', 5));
        return [
            'current' => $current,
            'previous' => $previous,
            'percentage' => round($percentage, 1),
            'direction' => $percentage > 0 ? 'UP' : ($percentage < 0 ? 'DOWN' : 'STABLE'),
            'status' => abs($percentage) < $stableMargin ? 'STABLE' : ($improved ? 'IMPROVING' : 'WORSENING'),
        ];
    }

    private function decorateClass(array $class): array
    {
        $score = min(100,
            ((int) $class['unjustified_absences'] * 3)
            + ((int) $class['total_occurrences'] * 4)
            + ((int) $class['serious_occurrences'] * 12)
        );
        $moderate = (int) $this->settings->get('intelligence.risk_moderate_threshold', 20);
        $high = (int) $this->settings->get('intelligence.risk_high_threshold', 45);
        $critical = (int) $this->settings->get('intelligence.risk_critical_threshold', 70);
        $classAttention = max(1, (int) $this->settings->get('intelligence.class_attention_threshold', 20));
        $class['risk_score'] = $score;
        $class['risk_level'] = match (true) {
            $score < $classAttention => 'LOW',
            $score >= $critical => 'CRITICAL',
            $score >= $high => 'HIGH',
            $score >= $moderate => 'MODERATE',
            default => 'MODERATE',
        };
        return $class;
    }

    private function recommendations(array $students, array $classes, int $staleCritical, array $attendanceTrend, array $occurrenceTrend): array
    {
        $items = [];
        $criticalStudents = array_values(array_filter($students, static fn(array $s): bool => $s['risk_level'] === 'CRITICAL'));
        if ($criticalStudents !== []) {
            $items[] = ['level' => 'CRITICAL', 'icon' => 'user-round-search', 'title' => 'Intervenção individual prioritária', 'text' => count($criticalStudents) . ' aluno(s) apresentam risco crítico combinado. ' . ((bool) $this->settings->get('intelligence.recommendation_guardian_contact', true) ? 'Recomenda-se análise da gestão e contato com responsáveis.' : 'Recomenda-se análise da gestão e definição de intervenção pedagógica.')];
        }
        if ($staleCritical > 0) {
            $items[] = ['level' => 'HIGH', 'icon' => 'clock-alert', 'title' => 'Ocorrências críticas sem resolução', 'text' => $staleCritical . ' ocorrência(s) crítica(s) permanecem abertas há mais de 7 dias.'];
        }
        if (($attendanceTrend['status'] ?? '') === 'WORSENING') {
            $items[] = ['level' => 'HIGH', 'icon' => 'trending-up', 'title' => 'Piora nas faltas sem justificativa', 'text' => 'As faltas sem justificativa cresceram ' . abs((float) $attendanceTrend['percentage']) . '% em relação aos 30 dias anteriores.'];
        }
        if (($occurrenceTrend['status'] ?? '') === 'WORSENING') {
            $items[] = ['level' => 'MODERATE', 'icon' => 'triangle-alert', 'title' => 'Crescimento de ocorrências', 'text' => 'O total de ocorrências cresceu ' . abs((float) $occurrenceTrend['percentage']) . '% no período analisado.'];
        }
        if ($classes !== []) {
            $top = $this->decorateClass($classes[0]);
            $items[] = ['level' => $top['risk_level'], 'icon' => 'school', 'title' => 'Turma que exige acompanhamento', 'text' => $top['name'] . ' concentra os sinais mais relevantes do período: ' . (int) $top['unjustified_absences'] . ' faltas sem justificativa e ' . (int) $top['total_occurrences'] . ' ocorrências.'];
        }
        if ($items === []) {
            $items[] = ['level' => 'LOW', 'icon' => 'badge-check', 'title' => 'Cenário estável', 'text' => 'Nenhum sinal prioritário foi identificado nos últimos 30 dias.'];
        }
        return array_slice($items, 0, 5);
    }
}
