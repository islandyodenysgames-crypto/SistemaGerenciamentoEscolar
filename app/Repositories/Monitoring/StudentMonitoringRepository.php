<?php

declare(strict_types=1);

namespace App\Repositories\Monitoring;

use App\Repositories\BaseRepository;

final class StudentMonitoringRepository extends BaseRepository
{
    public function activeForStudent(int $studentId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM student_monitoring WHERE student_id = :student_id AND status = 'ACTIVE' AND end_date >= CURDATE() ORDER BY id DESC LIMIT 1");
        $stmt->execute(['student_id' => $studentId]);
        return $stmt->fetch() ?: null;
    }

    public function latestForStudent(int $studentId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM student_monitoring WHERE student_id=:student_id ORDER BY id DESC LIMIT 1");
        $stmt->execute(['student_id'=>$studentId]);
        return $stmt->fetch() ?: null;
    }

    public function casesForStudent(int $studentId): array
    {
        $sql = "SELECT
                    sm.*,
                    COUNT(DISTINCT CASE WHEN smu.status='ACTIVE' THEN smu.user_id END) AS active_followers,
                    COUNT(DISTINCT smu.user_id) AS total_followers,
                    GROUP_CONCAT(DISTINCT u.name ORDER BY u.name SEPARATOR '||') AS follower_names,
                    (
                        SELECT actor.name
                        FROM student_monitoring_events closing_event
                        INNER JOIN users actor ON actor.id=closing_event.performed_by
                        WHERE closing_event.monitoring_id=sm.id
                          AND closing_event.event_type='NO_ACTIVE_FOLLOWERS'
                        ORDER BY closing_event.occurred_at DESC, closing_event.id DESC
                        LIMIT 1
                    ) AS closed_by_name,
                    (
                        SELECT closing_event.occurred_at
                        FROM student_monitoring_events closing_event
                        WHERE closing_event.monitoring_id=sm.id
                          AND closing_event.event_type='NO_ACTIVE_FOLLOWERS'
                        ORDER BY closing_event.occurred_at DESC, closing_event.id DESC
                        LIMIT 1
                    ) AS closed_at
                FROM student_monitoring sm
                LEFT JOIN student_monitoring_users smu ON smu.monitoring_id=sm.id
                LEFT JOIN users u ON u.id=smu.user_id
                WHERE sm.student_id=:student_id
                GROUP BY sm.id
                ORDER BY
                    (COUNT(DISTINCT CASE WHEN smu.status='ACTIVE' THEN smu.user_id END)>0) DESC,
                    CASE WHEN COUNT(DISTINCT CASE WHEN smu.status='ACTIVE' THEN smu.user_id END)>0 THEN COALESCE(sm.updated_at,sm.created_at) END DESC,
                    CASE WHEN COUNT(DISTINCT CASE WHEN smu.status='ACTIVE' THEN smu.user_id END)=0 THEN COALESCE((
                        SELECT MAX(e2.occurred_at) FROM student_monitoring_events e2 WHERE e2.monitoring_id=sm.id
                    ),sm.updated_at,sm.created_at) END DESC,
                    sm.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['student_id'=>$studentId]);
        $cases=$stmt->fetchAll();
        foreach($cases as &$case){
            $names=trim((string)($case['follower_names']??''));
            $case['followers']=$names!==''?array_values(array_filter(explode('||',$names))):[];
        }
        unset($case);
        return $cases;
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT sm.*, s.name AS student_name FROM student_monitoring sm INNER JOIN students s ON s.id = sm.student_id WHERE sm.id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("INSERT INTO student_monitoring (student_id,case_title,problem_code,problem_details,start_date,end_date,status,reason,created_by,created_at,updated_at) VALUES (:student_id,:case_title,:problem_code,:problem_details,:start_date,:end_date,'ACTIVE',:reason,:created_by,NOW(),NOW())");
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function addUser(int $monitoringId, int $userId, int $assignedBy, string $startDate, string $endDate, ?string $internalNotes): int
    {
        // Reativa o vínculo anterior em vez de criar registros duplicados.
        // Isso permite que alguém volte a acompanhar o mesmo aluno após encerrar sua participação.
        $check = $this->db->prepare("SELECT id, status FROM student_monitoring_users WHERE monitoring_id = :monitoring_id AND user_id = :user_id ORDER BY id DESC LIMIT 1");
        $check->execute(['monitoring_id' => $monitoringId, 'user_id' => $userId]);
        $existing = $check->fetch();

        if ($existing) {
            if (($existing['status'] ?? '') === 'ACTIVE') return (int) $existing['id'];

            $reactivate = $this->db->prepare("UPDATE student_monitoring_users SET assigned_by=:assigned_by, start_date=:start_date, end_date=:end_date, reason=NULL, internal_notes=:internal_notes, status='ACTIVE', assigned_at=NOW(), ended_at=NULL, updated_at=NOW() WHERE id=:id");
            $reactivate->execute([
                'assigned_by' => $assignedBy,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'internal_notes' => $internalNotes,
                'id' => (int) $existing['id'],
            ]);
            return (int) $existing['id'];
        }

        $stmt = $this->db->prepare("INSERT INTO student_monitoring_users (monitoring_id,user_id,assigned_by,start_date,end_date,reason,internal_notes,status,assigned_at,created_at,updated_at) VALUES (:monitoring_id,:user_id,:assigned_by,:start_date,:end_date,NULL,:internal_notes,'ACTIVE',NOW(),NOW(),NOW())");
        $stmt->execute(['monitoring_id' => $monitoringId, 'user_id' => $userId, 'assigned_by' => $assignedBy, 'start_date' => $startDate, 'end_date' => $endDate, 'internal_notes' => $internalNotes]);
        return (int) $this->db->lastInsertId();
    }

    public function activeUserLink(int $monitoringId, int $userId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM student_monitoring_users WHERE monitoring_id=:monitoring_id AND user_id=:user_id AND status='ACTIVE' AND start_date<=CURDATE() AND end_date>=CURDATE() ORDER BY id DESC LIMIT 1");
        $stmt->execute(['monitoring_id'=>$monitoringId,'user_id'=>$userId]);
        return $stmt->fetch() ?: null;
    }


    public function userLink(int $monitoringId, int $userId): ?array
    {
        $stmt=$this->db->prepare("SELECT * FROM student_monitoring_users WHERE monitoring_id=:monitoring_id AND user_id=:user_id ORDER BY id DESC LIMIT 1");
        $stmt->execute(['monitoring_id'=>$monitoringId,'user_id'=>$userId]);
        return $stmt->fetch() ?: null;
    }

    public function expiredActiveLinksForUser(int $userId): array
    {
        $stmt=$this->db->prepare("SELECT smu.*, sm.student_id, s.name AS student_name FROM student_monitoring_users smu INNER JOIN student_monitoring sm ON sm.id=smu.monitoring_id INNER JOIN students s ON s.id=sm.student_id WHERE smu.user_id=:user_id AND smu.status='ACTIVE' AND smu.end_date<CURDATE()");
        $stmt->execute(['user_id'=>$userId]);
        return $stmt->fetchAll();
    }

    public function concludeExpiredLink(int $linkId): void
    {
        $stmt=$this->db->prepare("UPDATE student_monitoring_users SET status='ENDED', ended_at=COALESCE(ended_at,CONCAT(end_date,' 23:59:59')), updated_at=NOW() WHERE id=:id AND status='ACTIVE' AND end_date<CURDATE()");
        $stmt->execute(['id'=>$linkId]);
    }

    public function closeCaseIfExpired(int $monitoringId): bool
    {
        $stmt=$this->db->prepare("UPDATE student_monitoring sm SET sm.status='COMPLETED',sm.updated_at=NOW() WHERE sm.id=:id AND sm.status='ACTIVE' AND sm.end_date<CURDATE() AND NOT EXISTS (SELECT 1 FROM student_monitoring_users smu WHERE smu.monitoring_id=sm.id AND smu.status='ACTIVE' AND smu.end_date>=CURDATE())");
        $stmt->execute(['id'=>$monitoringId]);
        return $stmt->rowCount()>0;
    }

    public function isMonitoringOpen(int $monitoringId): bool
    {
        $stmt=$this->db->prepare("SELECT COUNT(*) FROM student_monitoring_users WHERE monitoring_id=:id AND status='ACTIVE' AND start_date<=CURDATE() AND end_date>=CURDATE()");
        $stmt->execute(['id'=>$monitoringId]);
        return (int)$stmt->fetchColumn()>0;
    }

    public function endUser(int $monitoringId, int $userId): void
    {
        $stmt = $this->db->prepare("UPDATE student_monitoring_users SET status='ENDED', ended_at=NOW(), updated_at=NOW() WHERE monitoring_id=:monitoring_id AND user_id=:user_id AND status='ACTIVE'");
        $stmt->execute(['monitoring_id' => $monitoringId, 'user_id' => $userId]);
    }

    public function updateCaseStatus(int $monitoringId, string $status): void
    {
        $stmt=$this->db->prepare("UPDATE student_monitoring SET status=:status, updated_at=NOW() WHERE id=:id AND status<>:status_check");
        $stmt->execute(['status'=>$status,'id'=>$monitoringId,'status_check'=>$status]);
    }

    public function users(int $monitoringId): array
    {
        $stmt = $this->db->prepare("SELECT smu.*, u.name, u.role, assigner.name AS assigned_by_name FROM student_monitoring_users smu INNER JOIN users u ON u.id=smu.user_id LEFT JOIN users assigner ON assigner.id=smu.assigned_by WHERE smu.monitoring_id=:id ORDER BY smu.status='ACTIVE' DESC,u.name ASC");
        $stmt->execute(['id' => $monitoringId]);
        return $stmt->fetchAll();
    }



    public function userNameById(int $userId): ?string
    {
        $stmt=$this->db->prepare("SELECT name FROM users WHERE id=:id LIMIT 1");
        $stmt->execute(['id'=>$userId]);
        $name=$stmt->fetchColumn();
        return $name!==false ? (string)$name : null;
    }

    public function activeFollowersCount(int $monitoringId): int
    {
        $stmt=$this->db->prepare("SELECT COUNT(*) FROM student_monitoring_users WHERE monitoring_id=:id AND status='ACTIVE'");
        $stmt->execute(['id'=>$monitoringId]);
        return (int)$stmt->fetchColumn();
    }

    public function recordEvent(int $monitoringId,string $eventType,?int $targetUserId,int $performedBy,int $activeCount,?string $details=null): void
    {
        $stmt=$this->db->prepare("INSERT INTO student_monitoring_events (monitoring_id,event_type,target_user_id,performed_by,active_followers_count,details,occurred_at,created_at) VALUES (:monitoring_id,:event_type,:target_user_id,:performed_by,:active_count,:details,NOW(),NOW())");
        $stmt->execute([
            'monitoring_id'=>$monitoringId,
            'event_type'=>$eventType,
            'target_user_id'=>$targetUserId,
            'performed_by'=>$performedBy,
            'active_count'=>$activeCount,
            'details'=>$details,
        ]);
    }

    public function timelineForMonitoring(int $monitoringId): array
    {
        $stmt=$this->db->prepare("SELECT * FROM (
            SELECT sme.id, sme.event_type AS type, sme.occurred_at AS event_at,
                   target.name AS target_name, actor.name AS actor_name,
                   sme.active_followers_count, sme.details,
                   NULL AS action_label, NULL AS action_description, NULL AS author_name, NULL AS action_id
            FROM student_monitoring_events sme
            LEFT JOIN users target ON target.id=sme.target_user_id
            INNER JOIN users actor ON actor.id=sme.performed_by
            WHERE sme.monitoring_id=:events_monitoring
              AND sme.event_type NOT IN ('PLAN_UPDATED')
            UNION ALL
            SELECT sma.id, 'ACTION_CREATED' AS type, sma.created_at AS event_at,
                   NULL AS target_name, author.name AS actor_name, NULL AS active_followers_count,
                   CONCAT('Data da ação: ',DATE_FORMAT(sma.action_date,'%d/%m/%Y')) AS details,
                   sma.action_type_label AS action_label, sma.description AS action_description, author.name AS author_name, sma.id AS action_id
            FROM student_monitoring_actions sma
            INNER JOIN users author ON author.id=sma.created_by
            WHERE sma.monitoring_id=:actions_monitoring AND sma.deleted_at IS NULL
        ) timeline ORDER BY event_at DESC,id DESC");
        $stmt->execute(['events_monitoring'=>$monitoringId,'actions_monitoring'=>$monitoringId]);
        return $stmt->fetchAll();
    }

    public function allForUser(int $userId, bool $allSchool = false): array
    {
        $visibility = $allSchool
            ? '1=1'
            : "EXISTS (SELECT 1 FROM student_monitoring_users mine WHERE mine.monitoring_id=sm.id AND mine.user_id=:user_id)";

        $sql = "SELECT
                    sm.*,
                    current_link.id AS monitoring_user_id,
                    current_link.user_id AS linked_user_id,
                    current_link.status AS link_status,
                    current_link.ended_at,
                    s.name AS student_name,
                    sc.id AS class_id,
                    sc.name AS class_name,
                    sc.year AS class_year,
                    (SELECT COUNT(*) FROM student_monitoring_users p WHERE p.monitoring_id=sm.id) AS participants_count,
                    (SELECT COUNT(*) FROM student_monitoring_users p WHERE p.monitoring_id=sm.id AND p.status='ACTIVE') AS active_participants_count
                FROM student_monitoring sm
                INNER JOIN students s ON s.id=sm.student_id
                LEFT JOIN student_monitoring_users current_link
                    ON current_link.id=(
                        SELECT MAX(mine_link.id)
                        FROM student_monitoring_users mine_link
                        WHERE mine_link.monitoring_id=sm.id AND mine_link.user_id=:current_user_id
                    )
                LEFT JOIN enrollments e ON e.id=(
                    SELECT MAX(e2.id) FROM enrollments e2
                    WHERE e2.student_id=s.id AND e2.active=1
                )
                LEFT JOIN school_classes sc ON sc.id=e.school_class_id
                WHERE {$visibility}
                ORDER BY
                    (sm.status='ACTIVE' AND sm.end_date>=CURDATE()) DESC,
                    sm.end_date DESC,
                    sm.id DESC,
                    s.name ASC";
        $stmt=$this->db->prepare($sql);
        $params=['current_user_id'=>$userId];
        if(!$allSchool){$params['user_id']=$userId;}
        $stmt->execute($params);
        return $stmt->fetchAll();
    }


    public function activeStudentsForUser(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT DISTINCT sm.student_id, s.name AS student_name, COALESCE(smu.start_date,sm.start_date) AS start_date
            FROM student_monitoring sm
            INNER JOIN student_monitoring_users smu ON smu.monitoring_id=sm.id AND smu.status='ACTIVE'
            INNER JOIN students s ON s.id=sm.student_id AND s.active=1
            WHERE smu.user_id=:user_id
              AND sm.status='ACTIVE'
              AND COALESCE(smu.start_date,sm.start_date) <= CURDATE()
              AND COALESCE(smu.end_date,sm.end_date) >= CURDATE()
            ORDER BY s.name ASC");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }


    public function currentRecommendation(int $monitoringId): ?array
    {
        $stmt=$this->db->prepare("SELECT smr.*, u.name AS handled_by_name FROM student_monitoring_recommendations smr LEFT JOIN users u ON u.id=smr.handled_by WHERE smr.monitoring_id=:monitoring_id AND smr.status IN ('PENDING','IN_PROGRESS') ORDER BY smr.id DESC LIMIT 1");
        $stmt->execute(['monitoring_id'=>$monitoringId]);
        return $stmt->fetch() ?: null;
    }

    public function syncRecommendation(int $monitoringId,array $recommendation): array
    {
        $latestStmt=$this->db->prepare("SELECT * FROM student_monitoring_recommendations WHERE monitoring_id=:monitoring_id ORDER BY id DESC LIMIT 1");
        $latestStmt->execute(['monitoring_id'=>$monitoringId]);
        $latest=$latestStmt->fetch() ?: null;
        if($latest && (string)$latest['recommendation_code']===(string)$recommendation['code']){
            $stmt=$this->db->prepare("UPDATE student_monitoring_recommendations SET title=:title,reason=:reason,priority=:priority,updated_at=NOW() WHERE id=:id");
            $stmt->execute(['title'=>$recommendation['title'],'reason'=>$recommendation['reason'],'priority'=>$recommendation['priority'],'id'=>$latest['id']]);
            return $this->findRecommendation((int)$latest['id']) ?? $latest;
        }
        $current=$this->currentRecommendation($monitoringId);
        if($current){
            $stmt=$this->db->prepare("UPDATE student_monitoring_recommendations SET status='SUPERSEDED',handled_at=NOW(),updated_at=NOW() WHERE id=:id");
            $stmt->execute(['id'=>$current['id']]);
        }
        $stmt=$this->db->prepare("INSERT INTO student_monitoring_recommendations (monitoring_id,recommendation_code,title,reason,priority,status,generated_at,created_at,updated_at) VALUES (:monitoring_id,:code,:title,:reason,:priority,'PENDING',NOW(),NOW(),NOW())");
        $stmt->execute(['monitoring_id'=>$monitoringId,'code'=>$recommendation['code'],'title'=>$recommendation['title'],'reason'=>$recommendation['reason'],'priority'=>$recommendation['priority']]);
        return $this->findRecommendation((int)$this->db->lastInsertId()) ?? [];
    }

    public function findRecommendation(int $id): ?array
    {
        $stmt=$this->db->prepare("SELECT smr.*, sm.student_id FROM student_monitoring_recommendations smr INNER JOIN student_monitoring sm ON sm.id=smr.monitoring_id WHERE smr.id=:id LIMIT 1");
        $stmt->execute(['id'=>$id]);
        return $stmt->fetch() ?: null;
    }

    public function updateRecommendationStatus(int $id,string $status,int $userId,?string $notes): void
    {
        $stmt=$this->db->prepare("UPDATE student_monitoring_recommendations SET status=:status,resolution_notes=:notes,handled_by=:handled_by,handled_at=NOW(),updated_at=NOW() WHERE id=:id");
        $stmt->execute(['status'=>$status,'notes'=>$notes,'handled_by'=>$userId,'id'=>$id]);
    }

    public function completeCurrentRecommendation(int $monitoringId,int $userId,string $notes): void
    {
        $stmt=$this->db->prepare("UPDATE student_monitoring_recommendations SET status='COMPLETED',resolution_notes=:notes,handled_by=:user_id,handled_at=NOW(),updated_at=NOW() WHERE monitoring_id=:monitoring_id AND status IN ('PENDING','IN_PROGRESS')");
        $stmt->execute(['notes'=>$notes,'user_id'=>$userId,'monitoring_id'=>$monitoringId]);
    }

    public function latestActionDateForStudent(int $studentId): ?string
    {
        $stmt = $this->db->prepare("SELECT sma.action_date
            FROM student_monitoring_actions sma
            INNER JOIN student_monitoring sm ON sm.id=sma.monitoring_id
            WHERE sm.student_id=:student_id
              AND sma.deleted_at IS NULL
            ORDER BY sma.action_date DESC, sma.id DESC
            LIMIT 1");
        $stmt->execute(['student_id' => $studentId]);
        $value = $stmt->fetchColumn();
        return $value !== false ? (string)$value : null;
    }

    public function activeUserIdsByStudent(int $studentId): array
    {
        $stmt = $this->db->prepare("SELECT DISTINCT smu.user_id FROM student_monitoring sm INNER JOIN student_monitoring_users smu ON smu.monitoring_id=sm.id AND smu.status='ACTIVE' WHERE sm.student_id=:student_id AND sm.status='ACTIVE' AND COALESCE(smu.start_date,sm.start_date) <= CURDATE() AND COALESCE(smu.end_date,sm.end_date) >= CURDATE()");
        $stmt->execute(['student_id'=>$studentId]);
        return array_map('intval', array_column($stmt->fetchAll(), 'user_id'));
    }

    public function teachers(): array
    {
        $stmt = $this->db->query("SELECT id,name,email FROM users WHERE active=1 AND role='TEACHER' ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    public function planForLink(int $monitoringUserId): ?array
    {
        $stmt = $this->db->prepare("SELECT smp.* FROM student_monitoring_plans smp INNER JOIN student_monitoring_users smu ON smu.monitoring_id=smp.monitoring_id WHERE smu.id=:link_id LIMIT 1");
        $stmt->execute(['link_id'=>$monitoringUserId]);
        return $this->decodePlan($stmt->fetch() ?: null);
    }

    public function activePlanForMonitoring(int $monitoringId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM student_monitoring_plans WHERE monitoring_id=:monitoring_id AND status='ACTIVE' ORDER BY updated_at DESC,id DESC LIMIT 1");
        $stmt->execute(['monitoring_id'=>$monitoringId]);
        return $this->decodePlan($stmt->fetch() ?: null);
    }

    private function decodePlan(?array $plan): ?array
    {
        if ($plan) {
            $decoded=json_decode((string)($plan['strategies']??'[]'), true);
            $plan['strategies']=is_array($decoded)?$decoded:[];
        }
        return $plan;
    }

    public function savePlan(int $monitoringId, array $data): void
    {
        $stmt=$this->db->prepare("INSERT INTO student_monitoring_plans
            (monitoring_id,monitoring_user_id,objective_code,objective_label,strategies,target_metric,baseline_value,target_value,notes,status,created_by,created_at,updated_at)
            VALUES (:monitoring_id,NULL,:objective_code,:objective_label,:strategies,:target_metric,:baseline_value,:target_value,:notes,'ACTIVE',:created_by,NOW(),NOW())
            ON DUPLICATE KEY UPDATE objective_code=VALUES(objective_code), objective_label=VALUES(objective_label), strategies=VALUES(strategies), target_metric=VALUES(target_metric), baseline_value=VALUES(baseline_value), target_value=VALUES(target_value), notes=VALUES(notes), status='ACTIVE', updated_at=NOW()");
        $stmt->execute([
            'monitoring_id'=>$monitoringId,
            'objective_code'=>$data['objective_code'],
            'objective_label'=>$data['objective_label'],
            'strategies'=>json_encode($data['strategies'], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
            'target_metric'=>$data['target_metric'],
            'baseline_value'=>$data['baseline_value'],
            'target_value'=>$data['target_value'],
            'notes'=>$data['notes'],
            'created_by'=>$data['created_by'],
        ]);
    }

    public function periodMetrics(int $studentId, string $start, string $end): array
    {
        $stmt = $this->db->prepare("SELECT COUNT(ai.id) total_records, SUM(ai.status='P') total_presentes, SUM(ai.status='F') total_faltas FROM attendance_items ai INNER JOIN attendance a ON a.id=ai.attendance_id WHERE ai.student_id=:student_id AND a.attendance_date BETWEEN :start_date AND :end_date");
        $stmt->execute(['student_id'=>$studentId,'start_date'=>$start,'end_date'=>$end]);
        $row = $stmt->fetch() ?: [];
        $records=(int)($row['total_records']??0); $present=(int)($row['total_presentes']??0);
        $occ=$this->db->prepare("SELECT COUNT(*) total, SUM(status <> 'RESOLVED') open_total FROM student_occurrences WHERE student_id=:student_id AND occurrence_date BETWEEN :start_date AND :end_date");
        $occ->execute(['student_id'=>$studentId,'start_date'=>$start,'end_date'=>$end]);
        $orow=$occ->fetch()?:[];
        $resolved=max(0,(int)($orow['total']??0)-(int)($orow['open_total']??0));
        $justifiedStmt=$this->db->prepare("SELECT SUM(ai.status IN ('FJ','AM','FO')) justified_total FROM attendance_items ai INNER JOIN attendance a ON a.id=ai.attendance_id WHERE ai.student_id=:student_id AND a.attendance_date BETWEEN :start_date AND :end_date");
        $justifiedStmt->execute(['student_id'=>$studentId,'start_date'=>$start,'end_date'=>$end]);
        $justified=(int)(($justifiedStmt->fetch()?:[])['justified_total']??0);
        return ['total_records'=>$records,'total_presentes'=>$present,'total_faltas'=>(int)($row['total_faltas']??0),'justified_absences'=>$justified,'attendance_percentage'=>$records>0?round($present/$records*100,1):0,'total_occurrences'=>(int)($orow['total']??0),'open_occurrences'=>(int)($orow['open_total']??0),'resolved_occurrences'=>$resolved];
    }

    public function actionsForMonitoring(int $monitoringId): array
    {
        $stmt = $this->db->prepare("SELECT sma.*, smu.user_id AS link_user_id, u.name AS author_name
            FROM student_monitoring_actions sma
            INNER JOIN users u ON u.id=sma.created_by
            LEFT JOIN student_monitoring_users smu ON smu.id=sma.monitoring_user_id
            WHERE sma.monitoring_id=:monitoring_id AND sma.deleted_at IS NULL
            ORDER BY sma.action_date DESC, sma.created_at DESC, sma.id DESC");
        $stmt->execute(['monitoring_id'=>$monitoringId]);
        return $stmt->fetchAll();
    }

    public function findAction(int $actionId): ?array
    {
        $stmt = $this->db->prepare("SELECT sma.*, sma.monitoring_id, smu.user_id AS link_user_id, sm.student_id
            FROM student_monitoring_actions sma
            LEFT JOIN student_monitoring_users smu ON smu.id=sma.monitoring_user_id
            INNER JOIN student_monitoring sm ON sm.id=sma.monitoring_id
            WHERE sma.id=:id AND sma.deleted_at IS NULL LIMIT 1");
        $stmt->execute(['id'=>$actionId]);
        return $stmt->fetch() ?: null;
    }

    public function createAction(array $data): int
    {
        $stmt = $this->db->prepare("INSERT INTO student_monitoring_actions
            (monitoring_id,monitoring_user_id,action_date,action_type,action_type_label,description,result_status,issue_type,treatment_status,next_action,created_by,created_at,updated_at)
            VALUES (:monitoring_id,NULL,:action_date,:action_type,:action_type_label,:description,:result_status,:issue_type,:treatment_status,:next_action,:created_by,NOW(),NOW())");
        $stmt->execute($data);
        return (int)$this->db->lastInsertId();
    }

    public function updateAction(int $actionId, array $data, int $changedBy): void
    {
        $stmt = $this->db->prepare("UPDATE student_monitoring_actions SET
            action_date=:action_date, action_type=:action_type, action_type_label=:action_type_label,
            description=:description, result_status=:result_status, issue_type=:issue_type, treatment_status=:treatment_status, next_action=:next_action, updated_at=NOW()
            WHERE id=:id AND deleted_at IS NULL");
        $stmt->execute($data+['id'=>$actionId]);
    }

    public function softDeleteAction(int $actionId, int $changedBy): void
    {
        $stmt = $this->db->prepare("UPDATE student_monitoring_actions SET deleted_at=NOW(), updated_at=NOW() WHERE id=:id AND deleted_at IS NULL");
        $stmt->execute(['id'=>$actionId]);
    }

    public function extendMonitoring(int $monitoringId, string $newEndDate, string $reason, int $extendedBy): void
    {
        $stmt=$this->db->prepare("SELECT end_date FROM student_monitoring WHERE id=:id AND status='ACTIVE' LIMIT 1");
        $stmt->execute(['id'=>$monitoringId]);
        $monitoring=$stmt->fetch();
        if(!$monitoring) return;
        $this->db->beginTransaction();
        try{
            $history=$this->db->prepare("INSERT INTO student_monitoring_extensions (monitoring_id,monitoring_user_id,previous_end_date,new_end_date,reason,extended_by,created_at) VALUES (:monitoring_id,NULL,:previous,:new_end,:reason,:user,NOW())");
            $history->execute(['monitoring_id'=>$monitoringId,'previous'=>$monitoring['end_date'],'new_end'=>$newEndDate,'reason'=>$reason,'user'=>$extendedBy]);
            $update=$this->db->prepare("UPDATE student_monitoring SET end_date=:end_date, updated_at=NOW() WHERE id=:id");
            $update->execute(['end_date'=>$newEndDate,'id'=>$monitoringId]);
            $participants=$this->db->prepare("UPDATE student_monitoring_users SET end_date=:end_date, updated_at=NOW() WHERE monitoring_id=:id AND status='ACTIVE' AND end_date<:end_date");
            $participants->execute(['end_date'=>$newEndDate,'id'=>$monitoringId]);
            $this->db->commit();
        }catch(\Throwable $e){$this->db->rollBack();throw $e;}
    }

    public function extensionsForMonitoring(int $monitoringId): array
    {
        $stmt=$this->db->prepare("SELECT sme.*,u.name AS extended_by_name FROM student_monitoring_extensions sme INNER JOIN users u ON u.id=sme.extended_by WHERE sme.monitoring_id=:id ORDER BY sme.created_at DESC,sme.id DESC");
        $stmt->execute(['id'=>$monitoringId]);
        return $stmt->fetchAll();
    }


    public function actionIdsForLink(int $monitoringUserId): array
    {
        $stmt=$this->db->prepare("SELECT id FROM student_monitoring_actions WHERE monitoring_user_id=:id");
        $stmt->execute(['id'=>$monitoringUserId]);
        return array_map('intval',array_column($stmt->fetchAll(),'id'));
    }

    public function deleteConcludedLink(int $monitoringUserId): bool
    {
        $stmt=$this->db->prepare("SELECT id, monitoring_id, status, end_date FROM student_monitoring_users WHERE id=:id LIMIT 1");
        $stmt->execute(['id'=>$monitoringUserId]);
        $link=$stmt->fetch();
        if(!$link) return false;
        $isConcluded=(string)($link['status']??'')!=='ACTIVE' || ((string)($link['end_date']??'')!=='' && (string)$link['end_date']<date('Y-m-d'));
        if(!$isConcluded) return false;
        $delete=$this->db->prepare("DELETE FROM student_monitoring_users WHERE id=:id");
        $delete->execute(['id'=>$monitoringUserId]);
        return $delete->rowCount()>0;
    }


}
