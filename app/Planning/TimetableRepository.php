<?php
declare(strict_types=1);

namespace OrdiSchool\Planning;

use PDO;

final class TimetableRepository
{
    public function __construct(private readonly PDO $pdo, private readonly string $prefix)
    {
    }

    public function schoolsForPlanner(int $userId): array
    {
        $stmt = $this->pdo->prepare("SELECT DISTINCT s.id, s.name FROM {$this->prefix}schools s JOIN {$this->prefix}school_memberships m ON m.school_id=s.id JOIN {$this->prefix}role_bindings rb ON rb.membership_id=m.id WHERE m.user_id=:user_id AND m.status='active' AND m.valid_from<=CURDATE() AND (m.valid_until IS NULL OR m.valid_until>=CURDATE()) AND rb.role_key IN ('school_admin','planner') AND rb.valid_from<=CURDATE() AND (rb.valid_until IS NULL OR rb.valid_until>=CURDATE()) AND s.is_active=1 ORDER BY s.name");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function canPlan(int $userId, int $schoolId): bool
    {
        $stmt = $this->pdo->prepare("SELECT 1 FROM {$this->prefix}school_memberships m JOIN {$this->prefix}role_bindings rb ON rb.membership_id=m.id WHERE m.user_id=:user_id AND m.school_id=:school_id AND m.status='active' AND m.valid_from<=CURDATE() AND (m.valid_until IS NULL OR m.valid_until>=CURDATE()) AND rb.role_key IN ('school_admin','planner') AND rb.valid_from<=CURDATE() AND (rb.valid_until IS NULL OR rb.valid_until>=CURDATE()) LIMIT 1");
        $stmt->execute(['user_id' => $userId, 'school_id' => $schoolId]);
        return $stmt->fetchColumn() !== false;
    }

    public function canPublish(int $userId,int $schoolId): bool
    {
        $stmt=$this->pdo->prepare("SELECT 1 FROM {$this->prefix}school_memberships m JOIN {$this->prefix}role_bindings rb ON rb.membership_id=m.id WHERE m.user_id=:user_id AND m.school_id=:school_id AND m.status='active' AND m.valid_from<=CURDATE() AND (m.valid_until IS NULL OR m.valid_until>=CURDATE()) AND rb.role_key='school_admin' AND rb.valid_from<=CURDATE() AND (rb.valid_until IS NULL OR rb.valid_until>=CURDATE()) LIMIT 1");
        $stmt->execute(['user_id'=>$userId,'school_id'=>$schoolId]);
        return $stmt->fetchColumn()!==false;
    }

    public function listYears(int $schoolId): array
    {
        $stmt = $this->pdo->prepare("SELECT id,label,starts_on,ends_on,status FROM {$this->prefix}school_years WHERE school_id=:school_id ORDER BY starts_on DESC");
        $stmt->execute(['school_id' => $schoolId]);
        return $stmt->fetchAll();
    }

    public function listOfferings(int $schoolId): array
    {
        $stmt = $this->pdo->prepare("SELECT id,offering_key,offering_name,valid_from,valid_until,status FROM {$this->prefix}school_offerings WHERE school_id=:school_id ORDER BY offering_name");
        $stmt->execute(['school_id' => $schoolId]);
        return $stmt->fetchAll();
    }

    public function addOffering(int $schoolId, string $key, string $name, string $validFrom, ?string $validUntil, int $actor): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO {$this->prefix}school_offerings (school_id,offering_key,offering_name,valid_from,valid_until,status,created_at) VALUES (:school_id,:offering_key,:offering_name,:valid_from,:valid_until,'active',UTC_TIMESTAMP())");
        $stmt->execute(['school_id'=>$schoolId,'offering_key'=>$key,'offering_name'=>$name,'valid_from'=>$validFrom,'valid_until'=>$validUntil]);
        $this->audit($schoolId,$actor,'offering_created');
    }

    public function listClasses(int $schoolId, int $yearId): array
    {
        $stmt = $this->pdo->prepare("SELECT c.id,c.class_code,c.grade_level,c.group_type,c.expected_size,c.offering_id,o.offering_name FROM {$this->prefix}school_classes c JOIN {$this->prefix}school_offerings o ON o.id=c.offering_id WHERE c.school_id=:school_id AND c.school_year_id=:year_id AND c.is_active=1 ORDER BY o.offering_name,c.grade_level,c.class_code");
        $stmt->execute(['school_id'=>$schoolId,'year_id'=>$yearId]);
        return $stmt->fetchAll();
    }

    public function addClass(int $schoolId, int $yearId, int $offeringId, string $code, string $grade, string $type, int $actor, ?int $expectedSize=null): void
    {
        $check=$this->pdo->prepare("SELECT 1 FROM {$this->prefix}school_years y JOIN {$this->prefix}school_offerings o ON o.school_id=y.school_id WHERE y.id=:year_id AND y.school_id=:school_id AND o.id=:offering_id AND o.status='active' AND o.valid_from<=y.starts_on AND (o.valid_until IS NULL OR o.valid_until>=y.ends_on)");
        $check->execute(['year_id'=>$yearId,'school_id'=>$schoolId,'offering_id'=>$offeringId]);
        if($check->fetchColumn()===false)throw new PlanningException('Das Schuljahr oder das dafür gültige Schulformangebot gehört nicht zu dieser Schule.');
        $stmt=$this->pdo->prepare("INSERT INTO {$this->prefix}school_classes (school_id,school_year_id,offering_id,class_code,grade_level,group_type,expected_size,is_active,created_at) VALUES (:school_id,:year_id,:offering_id,:class_code,:grade_level,:group_type,:expected_size,1,UTC_TIMESTAMP())");
        $stmt->execute(['school_id'=>$schoolId,'year_id'=>$yearId,'offering_id'=>$offeringId,'class_code'=>$code,'grade_level'=>$grade,'group_type'=>$type,'expected_size'=>$expectedSize]);
        $this->audit($schoolId,$actor,'planning_class_created');
    }

    public function setClassExpectedSize(int $schoolId,int $classId,?int $expectedSize,int $actor): void
    {
        $stmt=$this->pdo->prepare("UPDATE {$this->prefix}school_classes SET expected_size=:size WHERE school_id=:school_id AND id=:id AND is_active=1");$stmt->execute(['size'=>$expectedSize,'school_id'=>$schoolId,'id'=>$classId]);
        if($stmt->rowCount()===0){$check=$this->pdo->prepare("SELECT 1 FROM {$this->prefix}school_classes WHERE school_id=:school_id AND id=:id AND is_active=1");$check->execute(['school_id'=>$schoolId,'id'=>$classId]);if($check->fetchColumn()===false)throw new PlanningException('Die Klasse oder der Kurs gehört nicht zu dieser Schule.');}
        $this->audit($schoolId,$actor,'planning_class_size_changed');
    }

    public function listSubjects(int $schoolId): array
    {
        $stmt=$this->pdo->prepare("SELECT id,subject_code,subject_name,preferred_room_type_key,preferred_room_type_name FROM {$this->prefix}school_subjects WHERE school_id=:school_id AND is_active=1 ORDER BY subject_name");
        $stmt->execute(['school_id'=>$schoolId]);
        return $stmt->fetchAll();
    }

    public function addSubject(int $schoolId,string $code,string $name,int $actor,?string $roomTypeKey=null,?string $roomTypeName=null): void
    {
        $stmt=$this->pdo->prepare("INSERT INTO {$this->prefix}school_subjects (school_id,subject_code,subject_name,preferred_room_type_key,preferred_room_type_name,is_active,created_at) VALUES (:school_id,:code,:name,:room_type_key,:room_type_name,1,UTC_TIMESTAMP())");
        $stmt->execute(['school_id'=>$schoolId,'code'=>$code,'name'=>$name,'room_type_key'=>$roomTypeKey,'room_type_name'=>$roomTypeName]);
        $this->audit($schoolId,$actor,'planning_subject_created');
    }

    public function setSubjectRoomPreference(int $schoolId,int $subjectId,?string $key,?string $name,int $actor): void
    {
        $stmt=$this->pdo->prepare("UPDATE {$this->prefix}school_subjects SET preferred_room_type_key=:key,preferred_room_type_name=:name WHERE school_id=:school_id AND id=:id AND is_active=1");
        $stmt->execute(['key'=>$key,'name'=>$name,'school_id'=>$schoolId,'id'=>$subjectId]);
        if($stmt->rowCount()===0){$check=$this->pdo->prepare("SELECT 1 FROM {$this->prefix}school_subjects WHERE school_id=:school_id AND id=:id AND is_active=1");$check->execute(['school_id'=>$schoolId,'id'=>$subjectId]);if($check->fetchColumn()===false)throw new PlanningException('Das Fach gehört nicht zu dieser Schule.');}
        $this->audit($schoolId,$actor,'planning_subject_room_preference_changed');
    }

    public function listRooms(int $schoolId, bool $activeOnly = true): array
    {
        $sql = "SELECT id,room_code,room_name,room_type_key,room_type_name,capacity,is_active FROM {$this->prefix}school_rooms WHERE school_id=:school_id" . ($activeOnly ? " AND is_active=1" : "") . " ORDER BY room_name";
        $stmt=$this->pdo->prepare($sql);$stmt->execute(['school_id'=>$schoolId]);return $stmt->fetchAll();
    }

    public function addRoom(int $schoolId,string $code,string $name,string $typeKey,string $typeName,?int $capacity,int $actor): void
    {
        $stmt=$this->pdo->prepare("INSERT INTO {$this->prefix}school_rooms (school_id,room_code,room_name,room_type_key,room_type_name,capacity,is_active,created_at) VALUES (:school_id,:code,:name,:type_key,:type_name,:capacity,1,UTC_TIMESTAMP())");
        $stmt->execute(['school_id'=>$schoolId,'code'=>$code,'name'=>$name,'type_key'=>$typeKey,'type_name'=>$typeName,'capacity'=>$capacity]);
        $this->audit($schoolId,$actor,'planning_room_created');
    }

    public function listPeriods(int $schoolId): array
    {
        $stmt=$this->pdo->prepare("SELECT period_number,label,starts_at,ends_at FROM {$this->prefix}timetable_periods WHERE school_id=:school_id AND is_active=1 ORDER BY period_number");
        $stmt->execute(['school_id'=>$schoolId]);
        return $stmt->fetchAll();
    }

    public function addPeriod(int $schoolId,int $number,string $label,string $start,string $end,int $actor): void
    {
        $overlap=$this->pdo->prepare("SELECT 1 FROM {$this->prefix}timetable_periods WHERE school_id=:school_id AND is_active=1 AND starts_at<:ends_at AND ends_at>:starts_at LIMIT 1");
        $overlap->execute(['school_id'=>$schoolId,'starts_at'=>$start,'ends_at'=>$end]);
        if($overlap->fetchColumn()!==false)throw new PlanningException('Die Unterrichtszeit überschneidet sich mit einer bereits angelegten Stunde.');
        $previous=$this->pdo->prepare("SELECT ends_at FROM {$this->prefix}timetable_periods WHERE school_id=:school_id AND period_number<:number AND is_active=1 ORDER BY period_number DESC LIMIT 1");
        $previous->execute(['school_id'=>$schoolId,'number'=>$number]);
        $previousEnd=$previous->fetchColumn();
        if($previousEnd!==false&&substr((string)$previousEnd,0,5)>$start)throw new PlanningException('Die Unterrichtsstunden müssen im Zeitraster auch zeitlich aufsteigend angelegt werden.');
        $next=$this->pdo->prepare("SELECT starts_at FROM {$this->prefix}timetable_periods WHERE school_id=:school_id AND period_number>:number AND is_active=1 ORDER BY period_number ASC LIMIT 1");
        $next->execute(['school_id'=>$schoolId,'number'=>$number]);
        $nextStart=$next->fetchColumn();
        if($nextStart!==false&&$end>substr((string)$nextStart,0,5))throw new PlanningException('Die Unterrichtsstunden müssen im Zeitraster auch zeitlich aufsteigend angelegt werden.');
        $stmt=$this->pdo->prepare("INSERT INTO {$this->prefix}timetable_periods (school_id,period_number,label,starts_at,ends_at,is_active,created_at) VALUES (:school_id,:number,:label,:starts_at,:ends_at,1,UTC_TIMESTAMP())");
        $stmt->execute(['school_id'=>$schoolId,'number'=>$number,'label'=>$label,'starts_at'=>$start,'ends_at'=>$end]);
        $this->audit($schoolId,$actor,'timetable_period_created');
    }

    public function listTeachers(int $schoolId): array
    {
        $stmt=$this->pdo->prepare("SELECT DISTINCT u.id,u.username FROM {$this->prefix}users u JOIN {$this->prefix}school_memberships m ON m.user_id=u.id JOIN {$this->prefix}role_bindings rb ON rb.membership_id=m.id WHERE m.school_id=:school_id AND m.status='active' AND m.valid_from<=CURDATE() AND (m.valid_until IS NULL OR m.valid_until>=CURDATE()) AND u.is_active=1 AND rb.role_key='teacher' AND rb.valid_from<=CURDATE() AND (rb.valid_until IS NULL OR rb.valid_until>=CURDATE()) ORDER BY u.username");
        $stmt->execute(['school_id'=>$schoolId]);
        return $stmt->fetchAll();
    }

    public function listPlans(int $schoolId,int $yearId): array
    {
        $stmt=$this->pdo->prepare("SELECT p.id,p.version_number,p.title,p.status,p.created_at,u.username AS creator FROM {$this->prefix}timetable_plans p JOIN {$this->prefix}users u ON u.id=p.created_by WHERE p.school_id=:school_id AND p.school_year_id=:year_id ORDER BY p.version_number DESC");
        $stmt->execute(['school_id'=>$schoolId,'year_id'=>$yearId]);
        return $stmt->fetchAll();
    }

    public function findPlan(int $schoolId,int $planId): ?array
    {
        $stmt=$this->pdo->prepare("SELECT id,school_id,school_year_id,version_number,title,status FROM {$this->prefix}timetable_plans WHERE school_id=:school_id AND id=:plan_id");
        $stmt->execute(['school_id'=>$schoolId,'plan_id'=>$planId]);
        $row=$stmt->fetch();
        return is_array($row)?$row:null;
    }

    public function createDraft(int $schoolId,int $yearId,string $title,int $actor,?int $basedOn=null): int
    {
        $yearCheck=$this->pdo->prepare("SELECT 1 FROM {$this->prefix}school_years WHERE id=:year_id AND school_id=:school_id");
        $yearCheck->execute(['year_id'=>$yearId,'school_id'=>$schoolId]);
        if($yearCheck->fetchColumn()===false)throw new PlanningException('Das Schuljahr gehört nicht zu dieser Schule.');
        $this->pdo->beginTransaction();
        try {
            $lock=$this->pdo->prepare("SELECT id FROM {$this->prefix}school_years WHERE id=:year_id AND school_id=:school_id FOR UPDATE");
            $lock->execute(['year_id'=>$yearId,'school_id'=>$schoolId]);
            if($lock->fetchColumn()===false)throw new PlanningException('Das Schuljahr gehört nicht zu dieser Schule.');
            $stmt=$this->pdo->prepare("SELECT COALESCE(MAX(version_number),0)+1 FROM {$this->prefix}timetable_plans WHERE school_year_id=:year_id");
            $stmt->execute(['year_id'=>$yearId]);
            $version=(int)$stmt->fetchColumn();
            if($basedOn!==null){
                $source=$this->findPlan($schoolId,$basedOn);
                if($source===null || $source['status']!=='published' || (int)$source['school_year_id']!==$yearId) throw new PlanningException('Nur ein freigegebener Plan desselben Schuljahres kann kopiert werden.');
            }
            $stmt=$this->pdo->prepare("INSERT INTO {$this->prefix}timetable_plans (school_id,school_year_id,version_number,title,status,based_on_plan_id,created_by,created_at) VALUES (:school_id,:year_id,:version,:title,'draft',:based_on,:actor,UTC_TIMESTAMP())");
            $stmt->execute(['school_id'=>$schoolId,'year_id'=>$yearId,'version'=>$version,'title'=>$title,'based_on'=>$basedOn,'actor'=>$actor]);
            $newId=(int)$this->pdo->lastInsertId();
            if($basedOn!==null){
                $stmt=$this->pdo->prepare("INSERT INTO {$this->prefix}timetable_lessons (school_id,plan_id,class_id,subject_id,teacher_user_id,room_id,weekday,start_period,duration_periods,created_at) SELECT school_id,:new_id,class_id,subject_id,teacher_user_id,room_id,weekday,start_period,duration_periods,UTC_TIMESTAMP() FROM {$this->prefix}timetable_lessons WHERE plan_id=:source_id");
                $stmt->execute(['new_id'=>$newId,'source_id'=>$basedOn]);
            }
            $this->audit($schoolId,$actor,'timetable_draft_created');
            $this->pdo->commit();
            return $newId;
        } catch (\Throwable $e) {
            if($this->pdo->inTransaction())$this->pdo->rollBack();
            throw $e;
        }
    }

    public function listLessons(int $schoolId,int $planId): array
    {
        $stmt=$this->pdo->prepare("SELECT l.id,l.class_id,c.class_code,c.grade_level,c.expected_size,c.is_active AS class_active,o.offering_name,o.status AS offering_status,o.valid_from AS offering_valid_from,o.valid_until AS offering_valid_until,sy.starts_on AS year_starts_on,sy.ends_on AS year_ends_on,l.subject_id,s.subject_code,s.subject_name,s.preferred_room_type_key,s.preferred_room_type_name,s.is_active AS subject_active,l.teacher_user_id,u.username,EXISTS(SELECT 1 FROM {$this->prefix}school_memberships m JOIN {$this->prefix}role_bindings rb ON rb.membership_id=m.id WHERE m.school_id=l.school_id AND m.user_id=l.teacher_user_id AND m.status='active' AND m.valid_from<=CURDATE() AND (m.valid_until IS NULL OR m.valid_until>=CURDATE()) AND rb.role_key='teacher' AND rb.valid_from<=CURDATE() AND (rb.valid_until IS NULL OR rb.valid_until>=CURDATE())) AS teacher_active,l.weekday,l.start_period,l.duration_periods,l.room_id,r.room_code,r.room_name,r.room_type_key AS actual_room_type_key,r.room_type_name AS actual_room_type_name,r.capacity AS room_capacity,r.is_active AS room_active,p.label,p.starts_at,p.ends_at,p.is_active AS period_active,EXISTS(SELECT 1 FROM {$this->prefix}timetable_periods p2 WHERE p2.school_id=l.school_id AND p2.period_number=l.start_period+1 AND p2.is_active=1) AS next_period_active FROM {$this->prefix}timetable_lessons l JOIN {$this->prefix}school_classes c ON c.id=l.class_id JOIN {$this->prefix}school_years sy ON sy.id=c.school_year_id JOIN {$this->prefix}school_offerings o ON o.id=c.offering_id JOIN {$this->prefix}school_subjects s ON s.id=l.subject_id JOIN {$this->prefix}users u ON u.id=l.teacher_user_id LEFT JOIN {$this->prefix}school_rooms r ON r.school_id=l.school_id AND r.id=l.room_id JOIN {$this->prefix}timetable_periods p ON p.school_id=l.school_id AND p.period_number=l.start_period WHERE l.school_id=:school_id AND l.plan_id=:plan_id ORDER BY l.weekday,l.start_period,o.offering_name,c.class_code");
        $stmt->execute(['school_id'=>$schoolId,'plan_id'=>$planId]);
        return $stmt->fetchAll();
    }

    public function addLesson(int $schoolId,int $planId,int $classId,int $subjectId,int $teacherId,int $weekday,int $period,int $duration,int $actor,?int $roomId=null): void
    {
        $plan=$this->findPlan($schoolId,$planId);
        if($plan===null || $plan['status']!=='draft')throw new PlanningException('Nur ein eigener Entwurf kann bearbeitet werden.');
        $classCheck=$this->pdo->prepare("SELECT 1 FROM {$this->prefix}school_classes WHERE id=:class_id AND school_id=:school_id AND school_year_id=:year_id AND is_active=1");
        $classCheck->execute(['class_id'=>$classId,'school_id'=>$schoolId,'year_id'=>$plan['school_year_id']]);
        if($classCheck->fetchColumn()===false)throw new PlanningException('Die Klasse oder der Kurs gehört nicht zum ausgewählten Schuljahr dieser Schule.');
        $subjectCheck=$this->pdo->prepare("SELECT 1 FROM {$this->prefix}school_subjects WHERE id=:subject_id AND school_id=:school_id AND is_active=1");
        $subjectCheck->execute(['subject_id'=>$subjectId,'school_id'=>$schoolId]);
        if($subjectCheck->fetchColumn()===false)throw new PlanningException('Das Fach gehört nicht zu dieser Schule.');
        $teacherCheck=$this->pdo->prepare("SELECT 1 FROM {$this->prefix}school_memberships m JOIN {$this->prefix}role_bindings rb ON rb.membership_id=m.id JOIN {$this->prefix}users u ON u.id=m.user_id WHERE m.school_id=:school_id AND m.user_id=:teacher_id AND m.status='active' AND m.valid_from<=CURDATE() AND (m.valid_until IS NULL OR m.valid_until>=CURDATE()) AND u.is_active=1 AND rb.role_key='teacher' AND rb.valid_from<=CURDATE() AND (rb.valid_until IS NULL OR rb.valid_until>=CURDATE()) LIMIT 1");
        $teacherCheck->execute(['school_id'=>$schoolId,'teacher_id'=>$teacherId]);
        if($teacherCheck->fetchColumn()===false)throw new PlanningException('Die ausgewählte Lehrkraft hat keine aktive Lehrkraft-Zuordnung an dieser Schule.');
        $expectedStmt=$this->pdo->prepare("SELECT expected_size FROM {$this->prefix}school_classes WHERE id=:class_id AND school_id=:school_id");$expectedStmt->execute(['class_id'=>$classId,'school_id'=>$schoolId]);$expectedSize=$expectedStmt->fetchColumn();
        if($roomId!==null){$roomStmt=$this->pdo->prepare("SELECT capacity FROM {$this->prefix}school_rooms WHERE id=:room_id AND school_id=:school_id AND is_active=1");$roomStmt->execute(['room_id'=>$roomId,'school_id'=>$schoolId]);$capacity=$roomStmt->fetchColumn();if($capacity===false)throw new PlanningException('Der ausgewählte Raum ist nicht aktiv oder gehört nicht zu dieser Schule.');if($capacity!==null&&$expectedSize!==false&&$expectedSize!==null&&(int)$capacity<(int)$expectedSize)throw new PlanningException('Die Raumkapazität ist kleiner als die hinterlegte Größe der Klasse/des Kurses.');}
        if($this->periodNumber($schoolId,$period)===null)throw new PlanningException('Die ausgewählte Unterrichtsstunde fehlt im Zeitraster.');
        if($this->periodNumber($schoolId,$period+$duration-1)===null)throw new PlanningException('Für die Doppelstunde fehlt die Folge-Stunde im Zeitraster.');
        $existing=$this->listLessons($schoolId,$planId);
        $candidate=['class_id'=>$classId,'teacher_user_id'=>$teacherId,'room_id'=>$roomId,'weekday'=>$weekday,'start_period'=>$period,'duration_periods'=>$duration];
        $conflicts=TimetableConflictDetector::detect(array_merge($existing,[$candidate]));
        if($conflicts!==[])throw new PlanningException(self::conflictMessage($conflicts[0]));
        $stmt=$this->pdo->prepare("INSERT INTO {$this->prefix}timetable_lessons (school_id,plan_id,class_id,subject_id,teacher_user_id,room_id,weekday,start_period,duration_periods,created_at) VALUES (:school_id,:plan_id,:class_id,:subject_id,:teacher_id,:room_id,:weekday,:period,:duration,UTC_TIMESTAMP())");
        $stmt->execute(['school_id'=>$schoolId,'plan_id'=>$planId,'class_id'=>$classId,'subject_id'=>$subjectId,'teacher_id'=>$teacherId,'room_id'=>$roomId,'weekday'=>$weekday,'period'=>$period,'duration'=>$duration]);
        $this->audit($schoolId,$actor,'timetable_lesson_created');
    }

    public function removeLesson(int $schoolId,int $planId,int $lessonId,int $actor): void
    {
        $plan=$this->findPlan($schoolId,$planId);
        if($plan===null || $plan['status']!=='draft')throw new PlanningException('Nur ein eigener Entwurf kann bearbeitet werden.');
        $stmt=$this->pdo->prepare("DELETE FROM {$this->prefix}timetable_lessons WHERE school_id=:school_id AND plan_id=:plan_id AND id=:id");
        $stmt->execute(['school_id'=>$schoolId,'plan_id'=>$planId,'id'=>$lessonId]);
        if($stmt->rowCount()>0)$this->audit($schoolId,$actor,'timetable_lesson_removed');
    }

    public function publish(int $schoolId,int $planId,int $actor): void
    {
        if(!$this->canPublish($actor,$schoolId))throw new PlanningException('Die Veröffentlichung erfordert eine aktive Schuladministrationsrolle an dieser Schule.');
        $plan=$this->findPlan($schoolId,$planId);
        if($plan===null || $plan['status']!=='draft')throw new PlanningException('Nur ein Entwurf kann freigegeben werden.');
        $lessons=$this->listLessons($schoolId,$planId);
        if($lessons===[])throw new PlanningException('Ein leerer Stundenplan kann nicht freigegeben werden.');
        foreach($lessons as $lesson){
            $offeringOutOfDate=$lesson['offering_status']!=='active'||$lesson['offering_valid_from']>$lesson['year_starts_on']||($lesson['offering_valid_until']!==null&&$lesson['offering_valid_until']<$lesson['year_ends_on']);
            $timeInvalid=(int)$lesson['period_active']!==1||((int)$lesson['duration_periods']===2&&(int)$lesson['next_period_active']!==1);
            $roomInvalid=$lesson['room_id']!==null&&((int)($lesson['room_active']??0)!==1||($lesson['room_capacity']!==null&&$lesson['expected_size']!==null&&(int)$lesson['room_capacity']<(int)$lesson['expected_size']));
            if((int)$lesson['class_active']!==1||(int)$lesson['subject_active']!==1||(int)$lesson['teacher_active']!==1||$offeringOutOfDate||$timeInvalid||$roomInvalid)throw new PlanningException('Der Entwurf enthält eine inzwischen ungültige Klasse, Schulform, Lehrkraft, Fach, Unterrichtszeit oder einen unzulässigen Raum. Bitte korrigiere den Entwurf vor der Freigabe.');
        }
        $conflicts=TimetableConflictDetector::detect($lessons);
        if($conflicts!==[])throw new PlanningException(self::conflictMessage($conflicts[0]));
        $this->pdo->beginTransaction();
        try{
            $stmt=$this->pdo->prepare("UPDATE {$this->prefix}timetable_plans SET status='superseded' WHERE school_id=:school_id AND school_year_id=:year_id AND status='published'");
            $stmt->execute(['school_id'=>$schoolId,'year_id'=>$plan['school_year_id']]);
            $stmt=$this->pdo->prepare("UPDATE {$this->prefix}timetable_plans SET status='published',approved_by=:actor,published_at=UTC_TIMESTAMP() WHERE school_id=:school_id AND id=:plan_id AND status='draft'");
            $stmt->execute(['actor'=>$actor,'school_id'=>$schoolId,'plan_id'=>$planId]);
            $this->audit($schoolId,$actor,'timetable_plan_published');
            $this->pdo->commit();
        }catch(\Throwable $e){if($this->pdo->inTransaction())$this->pdo->rollBack();throw $e;}
    }

    private function periodNumber(int $schoolId,int $number): ?int
    {
        $stmt=$this->pdo->prepare("SELECT period_number FROM {$this->prefix}timetable_periods WHERE school_id=:school_id AND period_number=:number AND is_active=1");
        $stmt->execute(['school_id'=>$schoolId,'number'=>$number]);
        $value=$stmt->fetchColumn();
        return $value===false?null:(int)$value;
    }

    private static function conflictMessage(array $conflict): string
    {
        $parts=[];if($conflict['class_conflict']??false)$parts[]='Klasse';if($conflict['teacher_conflict']??false)$parts[]='Lehrkraft';if($conflict['room_conflict']??false)$parts[]='Raum';$reason=implode(', ',$parts);
        return 'Zeitkonflikt: '.$reason.' ist am selben Wochentag in mindestens einer Unterrichtsstunde doppelt eingeplant. Schulformübergreifende Lehrkrafteinsätze werden gemeinsam geprüft.';
    }

    private function audit(int $schoolId,int $actor,string $event): void
    {
        $stmt=$this->pdo->prepare("INSERT INTO {$this->prefix}audit_log (school_id,actor_user_id,subject_user_id,event_key,role_key,created_at) VALUES (:school_id,:actor,NULL,:event,NULL,UTC_TIMESTAMP())");
        $stmt->execute(['school_id'=>$schoolId,'actor'=>$actor,'event'=>$event]);
    }
}
