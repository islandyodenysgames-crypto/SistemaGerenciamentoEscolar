<?php

declare(strict_types=1);

namespace App\Services\Content;

use App\Database\Connection;
use PDO;

final class StudentRecognitionService
{
    public function weekly(string $date): ?array
    {
        $start=date('Y-m-d',strtotime('monday this week',strtotime($date)));
        $sql="SELECT s.id student_id,s.name student_name,s.photo_path,s.photo_updated_at,sc.name class_name,COUNT(ai.id) records,ROUND(100*SUM(ai.status='P')/NULLIF(COUNT(ai.id),0),1) attendance_percentage FROM students s INNER JOIN enrollments e ON e.student_id=s.id AND e.active=1 INNER JOIN school_classes sc ON sc.id=e.school_class_id INNER JOIN attendance_items ai ON ai.student_id=s.id INNER JOIN attendance a ON a.id=ai.attendance_id AND a.attendance_date BETWEEN :start AND :end WHERE s.active=1 GROUP BY s.id,s.name,s.photo_path,s.photo_updated_at,sc.name HAVING COUNT(ai.id)>0 ORDER BY attendance_percentage DESC,records DESC,s.name ASC LIMIT 1";
        $stmt=Connection::getInstance()->prepare($sql);$stmt->execute(['start'=>$start,'end'=>$date]);$row=$stmt->fetch(PDO::FETCH_ASSOC);
        if(!$row)return null;$row['attendance_percentage']=(float)$row['attendance_percentage'];$row['records']=(int)$row['records'];return $row;
    }
}
