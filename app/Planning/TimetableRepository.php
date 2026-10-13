<?php
declare(strict_types=1);

namespace OrdiSchool\Planning;

use PDO;

final class TimetableRepository
{
    public function __construct(private readonly PDO $pdo, private readonly string $prefix, private readonly string $globalRole='member')
    {
    }

    public function schoolsForPlanner(int $userId): array
    {
        if($this->globalRole==='system_admin')return $this->pdo->query("SELECT id,name FROM {$this->prefix}schools WHERE is_active=1 ORDER BY name")->fetchAll();
        $stmt=$this->pdo->prepare("SELECT DISTINCT s.id,s.name FROM {$this->prefix}schools s JOIN {$this->prefix}school_memberships m ON m.school_id=s.id JOIN {$this->prefix}role_bindings rb ON rb.membership_id=m.id JOIN {$this->prefix}school_role_permissions rp ON rp.school_id=m.school_id AND rp.role_key=rb.role_key WHERE m.user_id=:user_id AND m.status='active' AND m.valid_from<=CURDATE() AND (m.valid_until IS NULL OR m.valid_until>=CURDATE()) AND rb.valid_from<=CURDATE() AND (rb.valid_until IS NULL OR rb.valid_until>=CURDATE()) AND rb.scope_id=0 AND rp.permission_key IN ('planning.view','planning.edit','planning.configure') AND rp.is_allowed=1 AND s.is_active=1 ORDER BY s.name");$stmt->execute(['user_id'=>$userId]);return $stmt->fetchAll();
    }

    private function hasPermission(int $userId,int $schoolId,string $permission): bool
    {
        if($this->globalRole==='system_admin')return true;
        $stmt=$this->pdo->prepare("SELECT 1 FROM {$this->prefix}school_memberships m JOIN {$this->prefix}role_bindings rb ON rb.membership_id=m.id JOIN {$this->prefix}school_role_permissions rp ON rp.school_id=m.school_id AND rp.role_key=rb.role_key WHERE m.user_id=:user_id AND m.school_id=:school_id AND m.status='active' AND m.valid_from<=CURDATE() AND (m.valid_until IS NULL OR m.valid_until>=CURDATE()) AND rb.valid_from<=CURDATE() AND (rb.valid_until IS NULL OR rb.valid_until>=CURDATE()) AND rb.scope_id=0 AND rp.permission_key=:permission AND rp.is_allowed=1 LIMIT 1");$stmt->execute(['user_id'=>$userId,'school_id'=>$schoolId,'permission'=>$permission]);return $stmt->fetchColumn()!==false;
    }

    public function canManageSetupPath(int $userId,int $schoolId,string $path): bool
    {
        if($this->globalRole==='system_admin')return true;
        $permissions=match($path){
            '/admin/planning/building','/admin/planning/room'=>['buildings.manage','planning.configure'],
            '/admin/planning/subject','/admin/planning/subject-room-preference'=>['subjects.manage','planning.configure'],
            '/admin/planning/class','/admin/planning/class-size','/admin/planning/class-leadership'=>['classes.manage','planning.configure'],
            default=>['planning.configure'],
        };
        foreach($permissions as $permission)if($this->hasPermission($userId,$schoolId,$permission))return true;return false;
    }
    public function canPlan(int $userId,int $schoolId): bool{return $this->hasPermission($userId,$schoolId,'planning.view')||$this->hasPermission($userId,$schoolId,'planning.edit')||$this->hasPermission($userId,$schoolId,'planning.configure');}
    public function canEdit(int $userId,int $schoolId): bool{return $this->hasPermission($userId,$schoolId,'planning.edit');}
    public function canManageSetup(int $userId,int $schoolId): bool{return $this->hasPermission($userId,$schoolId,'planning.configure')||$this->hasPermission($userId,$schoolId,'buildings.manage')||$this->hasPermission($userId,$schoolId,'subjects.manage')||$this->hasPermission($userId,$schoolId,'classes.manage');}
    public function canPublish(int $userId,int $schoolId): bool{return $this->hasPermission($userId,$schoolId,'planning.approve');}

    public function listYears(int $schoolId): array
    {
        $stmt = $this->pdo->prepare("SELECT id,label,starts_on,ends_on,status FROM {$this->prefix}school_years WHERE school_id=:school_id ORDER BY starts_on DESC");
        $stmt->execute(['school_id' => $schoolId]);
        return $stmt->fetchAll();
    }

    public function planningReadiness(int $schoolId,int $yearId): array
    {
        $count=function(string $sql,array $args): int{$stmt=$this->pdo->prepare($sql);$stmt->execute($args);return (int)$stmt->fetchColumn();};
        return ['buildings'=>$count("SELECT COUNT(*) FROM {$this->prefix}school_buildings WHERE school_id=:school_id AND is_active=1",['school_id'=>$schoolId]),'rooms'=>$count("SELECT COUNT(*) FROM {$this->prefix}school_rooms WHERE school_id=:school_id AND is_active=1",['school_id'=>$schoolId]),'subjects'=>$count("SELECT COUNT(*) FROM {$this->prefix}school_subjects WHERE school_id=:school_id AND is_active=1",['school_id'=>$schoolId]),'classes'=>$count("SELECT COUNT(*) FROM {$this->prefix}school_classes WHERE school_id=:school_id AND school_year_id=:year_id AND is_active=1 AND class_leader_user_id IS NOT NULL AND deputy_class_leader_user_id IS NOT NULL",['school_id'=>$schoolId,'year_id'=>$yearId]),'teachers'=>$count("SELECT COUNT(DISTINCT m.user_id) FROM {$this->prefix}school_memberships m JOIN {$this->prefix}role_bindings rb ON rb.membership_id=m.id WHERE m.school_id=:school_id AND m.status='active' AND rb.role_key='teacher' AND rb.valid_from<=CURDATE() AND (rb.valid_until IS NULL OR rb.valid_until>=CURDATE())",['school_id'=>$schoolId])];
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
        $stmt = $this->pdo->prepare("SELECT c.id,c.class_code,c.grade_level,c.group_type,c.expected_size,c.class_leader_user_id,c.deputy_class_leader_user_id,lead.username AS class_leader,deputy.username AS class_deputy,c.offering_id,o.offering_name FROM {$this->prefix}school_classes c JOIN {$this->prefix}school_offerings o ON o.id=c.offering_id LEFT JOIN {$this->prefix}users lead ON lead.id=c.class_leader_user_id LEFT JOIN {$this->prefix}users deputy ON deputy.id=c.deputy_class_leader_user_id WHERE c.school_id=:school_id AND c.school_year_id=:year_id AND c.is_active=1 ORDER BY o.offering_name,c.grade_level,c.class_code");
        $stmt->execute(['school_id'=>$schoolId,'year_id'=>$yearId]);
        return $stmt->fetchAll();
    }

    public function addClass(int $schoolId,int $yearId,int $offeringId,string $code,string $grade,string $type,int $actor,?int $expectedSize=null,?int $leadTeacherId=null,?int $deputyTeacherId=null): void
    {
        $check=$this->pdo->prepare("SELECT 1 FROM {$this->prefix}school_years y JOIN {$this->prefix}school_offerings o ON o.school_id=y.school_id WHERE y.id=:year_id AND y.school_id=:school_id AND o.id=:offering_id AND o.status='active' AND o.valid_from<=y.starts_on AND (o.valid_until IS NULL OR o.valid_until>=y.ends_on)");$check->execute(['year_id'=>$yearId,'school_id'=>$schoolId,'offering_id'=>$offeringId]);if($check->fetchColumn()===false)throw new PlanningException('Das Schuljahr oder Schulformangebot gehört nicht zu dieser Schule.');
        foreach([$leadTeacherId,$deputyTeacherId] as $teacherId){if($teacherId!==null){$t=$this->pdo->prepare("SELECT 1 FROM {$this->prefix}school_memberships m JOIN {$this->prefix}role_bindings rb ON rb.membership_id=m.id WHERE m.school_id=:school_id AND m.user_id=:user_id AND m.status='active' AND rb.role_key='teacher' AND rb.scope_id IN (0,:offering_id) AND rb.valid_from<=CURDATE() AND (rb.valid_until IS NULL OR rb.valid_until>=CURDATE())");$t->execute(['school_id'=>$schoolId,'user_id'=>$teacherId,'offering_id'=>$offeringId]);if($t->fetchColumn()===false)throw new PlanningException('Klassenleitung und Stellvertretung müssen aktive Lehrkräfte dieser Schulform sein.');}}
        if($leadTeacherId!==null&&$leadTeacherId===$deputyTeacherId)throw new PlanningException('Klassenleitung und Stellvertretung müssen unterschiedliche Personen sein.');
        $stmt=$this->pdo->prepare("INSERT INTO {$this->prefix}school_classes (school_id,school_year_id,offering_id,class_code,grade_level,group_type,expected_size,class_leader_user_id,deputy_class_leader_user_id,is_active,created_at) VALUES (:school_id,:year_id,:offering_id,:class_code,:grade_level,:group_type,:expected_size,:lead,:deputy,1,UTC_TIMESTAMP())");$stmt->execute(['school_id'=>$schoolId,'year_id'=>$yearId,'offering_id'=>$offeringId,'class_code'=>$code,'grade_level'=>$grade,'group_type'=>$type,'expected_size'=>$expectedSize,'lead'=>$leadTeacherId,'deputy'=>$deputyTeacherId]);$this->audit($schoolId,$actor,'planning_class_created');
    }

    public function setClassLeadership(int $schoolId,int $classId,int $leadTeacherId,int $deputyTeacherId,int $actor): void
    {
        if($leadTeacherId===$deputyTeacherId)throw new PlanningException('Klassenleitung und Stellvertretung müssen unterschiedliche Personen sein.');
        $class=$this->pdo->prepare("SELECT offering_id FROM {$this->prefix}school_classes WHERE school_id=:school_id AND id=:id AND is_active=1");$class->execute(['school_id'=>$schoolId,'id'=>$classId]);$offeringId=$class->fetchColumn();if($offeringId===false)throw new PlanningException('Die Klasse oder der Kurs gehört nicht zu dieser Schule.');
        foreach([$leadTeacherId,$deputyTeacherId] as $teacherId){$stmt=$this->pdo->prepare("SELECT 1 FROM {$this->prefix}school_memberships m JOIN {$this->prefix}role_bindings rb ON rb.membership_id=m.id WHERE m.school_id=:school_id AND m.user_id=:user_id AND m.status='active' AND rb.role_key='teacher' AND rb.scope_id IN (0,:offering_id) AND rb.valid_from<=CURDATE() AND (rb.valid_until IS NULL OR rb.valid_until>=CURDATE())");$stmt->execute(['school_id'=>$schoolId,'user_id'=>$teacherId,'offering_id'=>(int)$offeringId]);if($stmt->fetchColumn()===false)throw new PlanningException('Klassenleitung und Stellvertretung müssen aktive Lehrkräfte dieser Schulform sein.');}
        $update=$this->pdo->prepare("UPDATE {$this->prefix}school_classes SET class_leader_user_id=:lead,deputy_class_leader_user_id=:deputy WHERE school_id=:school_id AND id=:id");$update->execute(['lead'=>$leadTeacherId,'deputy'=>$deputyTeacherId,'school_id'=>$schoolId,'id'=>$classId]);$this->audit($schoolId,$actor,'planning_class_leadership_changed');
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

    public function listBuildings(int $schoolId): array
    {
        $stmt=$this->pdo->prepare("SELECT id,building_code,building_name,is_active FROM {$this->prefix}school_buildings WHERE school_id=:school_id AND is_active=1 ORDER BY building_name");$stmt->execute(['school_id'=>$schoolId]);return $stmt->fetchAll();
    }

    public function addBuilding(int $schoolId,string $code,string $name,int $actor): void
    {
        $stmt=$this->pdo->prepare("INSERT INTO {$this->prefix}school_buildings (school_id,building_code,building_name,is_active,created_at) VALUES (:school_id,:code,:name,1,UTC_TIMESTAMP())");$stmt->execute(['school_id'=>$schoolId,'code'=>$code,'name'=>$name]);$this->audit($schoolId,$actor,'school_building_created');
    }

    public function listRooms(int $schoolId, bool $activeOnly = true): array
    {
        $sql = "SELECT r.id,r.room_code,r.room_name,r.room_type_key,r.room_type_name,r.capacity,r.is_active,r.building_id,b.building_name FROM {$this->prefix}school_rooms r JOIN {$this->prefix}school_buildings b ON b.school_id=r.school_id AND b.id=r.building_id WHERE r.school_id=:school_id" . ($activeOnly ? " AND is_active=1" : "") . " ORDER BY room_name";
        $stmt=$this->pdo->prepare($sql);$stmt->execute(['school_id'=>$schoolId]);return $stmt->fetchAll();
    }

    public function addRoom(int $schoolId,int $buildingId,string $code,string $name,string $typeKey,string $typeName,?int $capacity,int $actor): void
    {
        $stmt=$this->pdo->prepare("INSERT INTO {$this->prefix}school_rooms (school_id,building_id,room_code,room_name,room_type_key,room_type_name,capacity,is_active,created_at) VALUES (:school_id,:building_id,:code,:name,:type_key,:type_name,:capacity,1,UTC_TIMESTAMP())");
        $stmt->execute(['school_id'=>$schoolId,'building_id'=>$buildingId,'code'=>$code,'name'=>$name,'type_key'=>$typeKey,'type_name'=>$typeName,'capacity'=>$capacity]);
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
        $stmt=$this->pdo->prepare("SELECT DISTINCT u.id,u.username,u.first_name,u.last_name FROM {$this->prefix}users u JOIN {$this->prefix}school_memberships m ON m.user_id=u.id JOIN {$this->prefix}role_bindings rb ON rb.membership_id=m.id WHERE m.school_id=:school_id AND m.status='active' AND m.valid_from<=CURDATE() AND (m.valid_until IS NULL OR m.valid_until>=CURDATE()) AND u.is_active=1 AND rb.role_key='teacher' AND rb.valid_from<=CURDATE() AND (rb.valid_until IS NULL OR rb.valid_until>=CURDATE()) ORDER BY u.last_name,u.first_name,u.username");
        $stmt->execute(['school_id'=>$schoolId]);
        return $stmt->fetchAll();
    }

    public function createRuleProfile(int $schoolId,int $yearId,int $offeringId,string $cohort,string $source,?string $url,?string $reference,string $checkedOn,int $actor): int
    {
        $this->pdo->beginTransaction();try{$scope=$this->pdo->prepare("SELECT 1 FROM {$this->prefix}school_years y JOIN {$this->prefix}school_offerings o ON o.school_id=y.school_id WHERE y.id=:year_id AND y.school_id=:school_id AND o.id=:offering_id");$scope->execute(['year_id'=>$yearId,'school_id'=>$schoolId,'offering_id'=>$offeringId]);if($scope->fetchColumn()===false)throw new PlanningException('Schuljahr und Schulform müssen derselben Schule angehören.');
            $yearLock=$this->pdo->prepare("SELECT id FROM {$this->prefix}school_years WHERE id=:year_id AND school_id=:school_id FOR UPDATE");$yearLock->execute(['year_id'=>$yearId,'school_id'=>$schoolId]);
            $lock=$this->pdo->prepare("SELECT COALESCE(MAX(version_number),0)+1 FROM {$this->prefix}planning_rule_profiles WHERE school_year_id=:year_id AND offering_id=:offering_id");$lock->execute(['year_id'=>$yearId,'offering_id'=>$offeringId]);$version=(int)$lock->fetchColumn();
            $stmt=$this->pdo->prepare("INSERT INTO {$this->prefix}planning_rule_profiles (school_id,school_year_id,offering_id,version_number,cohort_label,source_title,source_url,source_reference,source_checked_on,status,created_by,created_at) VALUES (:school_id,:year_id,:offering_id,:version,:cohort,:source,:url,:reference,:checked,'draft',:actor,UTC_TIMESTAMP())");$stmt->execute(['school_id'=>$schoolId,'year_id'=>$yearId,'offering_id'=>$offeringId,'version'=>$version,'cohort'=>$cohort,'source'=>$source,'url'=>$url,'reference'=>$reference,'checked'=>$checkedOn,'actor'=>$actor]);$id=(int)$this->pdo->lastInsertId();$this->pdo->prepare("UPDATE {$this->prefix}timetable_plans SET rules_review_required=1 WHERE school_id=:school_id AND school_year_id=:year_id")->execute(['school_id'=>$schoolId,'year_id'=>$yearId]);$this->audit($schoolId,$actor,'planning_rule_profile_created');$this->pdo->commit();return $id;
        }catch(\Throwable $e){if($this->pdo->inTransaction())$this->pdo->rollBack();throw $e;}
    }

    public function listRuleProfiles(int $schoolId,int $yearId): array
    {
        $stmt=$this->pdo->prepare("SELECT p.id,p.offering_id,p.version_number,p.cohort_label,p.source_title,p.source_url,p.source_reference,p.source_checked_on,p.status,p.reviewed_at,o.offering_name,u.username AS reviewer FROM {$this->prefix}planning_rule_profiles p JOIN {$this->prefix}school_offerings o ON o.school_id=p.school_id AND o.id=p.offering_id LEFT JOIN {$this->prefix}users u ON u.id=p.reviewed_by WHERE p.school_id=:school_id AND p.school_year_id=:year_id ORDER BY o.offering_name,p.version_number DESC");$stmt->execute(['school_id'=>$schoolId,'year_id'=>$yearId]);return $stmt->fetchAll();
    }

    public function reviewRuleProfile(int $schoolId,int $profileId,int $actor): void
    {
        $stmt=$this->pdo->prepare("UPDATE {$this->prefix}planning_rule_profiles SET status='reviewed',reviewed_by=:actor,reviewed_at=UTC_TIMESTAMP() WHERE school_id=:school_id AND id=:id AND status='draft'");$stmt->execute(['actor'=>$actor,'school_id'=>$schoolId,'id'=>$profileId]);if($stmt->rowCount()===0)throw new PlanningException('Nur ein noch nicht geprüftes Regelprofil kann markiert werden.');$scope=$this->pdo->prepare("SELECT school_year_id FROM {$this->prefix}planning_rule_profiles WHERE school_id=:school_id AND id=:id");$scope->execute(['school_id'=>$schoolId,'id'=>$profileId]);$year=$scope->fetchColumn();$this->pdo->prepare("UPDATE {$this->prefix}timetable_plans SET rules_review_required=1 WHERE school_id=:school_id AND school_year_id=:year_id")->execute(['school_id'=>$schoolId,'year_id'=>$year]);$this->audit($schoolId,$actor,'planning_rule_profile_reviewed');
    }

    public function recheckPlanRules(int $schoolId,int $yearId,int $planId,int $actor): void
    {
        $plan=$this->findPlan($schoolId,$planId);if($plan===null||(int)$plan['school_year_id']!==$yearId)throw new PlanningException('Der Plan gehört nicht zu diesem Schuljahr.');$lessons=$this->listLessons($schoolId,$planId);$offerings=[];foreach($lessons as $lesson)$offerings[(int)$lesson['offering_id']]=(string)$lesson['offering_name'];if($offerings===[])throw new PlanningException('Ein leerer Plan kann nicht auf Regelprofile geprüft werden.');
        $this->pdo->beginTransaction();try{$this->pdo->prepare("DELETE FROM {$this->prefix}timetable_plan_rule_reviews WHERE school_id=:school_id AND plan_id=:plan_id")->execute(['school_id'=>$schoolId,'plan_id'=>$planId]);$insert=$this->pdo->prepare("INSERT INTO {$this->prefix}timetable_plan_rule_reviews (school_id,plan_id,offering_id,rule_profile_id,profile_version,checked_by,checked_at) VALUES (:school_id,:plan_id,:offering_id,:profile_id,:version,:actor,UTC_TIMESTAMP())");
            foreach($offerings as $offeringId=>$name){$stmt=$this->pdo->prepare("SELECT id,version_number FROM {$this->prefix}planning_rule_profiles WHERE school_id=:school_id AND school_year_id=:year_id AND offering_id=:offering_id AND status='reviewed' ORDER BY version_number DESC LIMIT 1");$stmt->execute(['school_id'=>$schoolId,'year_id'=>$yearId,'offering_id'=>$offeringId]);$profile=$stmt->fetch();if(!is_array($profile))throw new PlanningException('Für '.$name.' fehlt ein fachlich geprüftes Regelprofil dieses Schuljahres.');$insert->execute(['school_id'=>$schoolId,'plan_id'=>$planId,'offering_id'=>$offeringId,'profile_id'=>$profile['id'],'version'=>$profile['version_number'],'actor'=>$actor]);}
            $this->pdo->prepare("UPDATE {$this->prefix}timetable_plans SET rules_review_required=0 WHERE school_id=:school_id AND id=:plan_id")->execute(['school_id'=>$schoolId,'plan_id'=>$planId]);$this->audit($schoolId,$actor,'timetable_rule_profiles_rechecked');$this->pdo->commit();
        }catch(\Throwable $e){if($this->pdo->inTransaction())$this->pdo->rollBack();throw $e;}
    }

    public function ruleReport(int $schoolId,int $yearId,int $planId,array $lessons): array
    {
        $hard=count(TimetableConflictDetector::detect($lessons));$soft=0;$used=[];foreach($lessons as $lesson){$used[(int)$lesson['offering_id']]=(string)$lesson['offering_name'];if(($lesson['room_id']===null&&(string)($lesson['preferred_room_type_key']??'')!=='')||($lesson['room_id']!==null&&(string)($lesson['preferred_room_type_key']??'')!==''&&(string)($lesson['actual_room_type_key']??'')!==(string)$lesson['preferred_room_type_key']))$soft++;}
        $saved=$this->pdo->prepare("SELECT r.offering_id,r.rule_profile_id,r.profile_version,p.status,p.version_number,p.cohort_label,p.source_title,p.source_url,p.source_reference,p.source_checked_on,p.reviewed_at,u.username AS reviewer FROM {$this->prefix}timetable_plan_rule_reviews r JOIN {$this->prefix}planning_rule_profiles p ON p.school_id=r.school_id AND p.id=r.rule_profile_id LEFT JOIN {$this->prefix}users u ON u.id=r.checked_by WHERE r.school_id=:school_id AND r.plan_id=:plan_id");$saved->execute(['school_id'=>$schoolId,'plan_id'=>$planId]);$rows=[];foreach($saved->fetchAll() as $row)$rows[(int)$row['offering_id']]=$row;
        $unresolved=0;$details='<ul>';foreach($used as $offeringId=>$name){$row=$rows[$offeringId]??null;if($row!==null){$latest=$this->pdo->prepare("SELECT id,status FROM {$this->prefix}planning_rule_profiles WHERE school_id=:school_id AND school_year_id=:year_id AND offering_id=:offering_id ORDER BY version_number DESC LIMIT 1");$latest->execute(['school_id'=>$schoolId,'year_id'=>$yearId,'offering_id'=>$offeringId]);$current=$latest->fetch();if(!is_array($current)||(int)$current['id']!==(int)$row['rule_profile_id']||$current['status']!=='reviewed')$row=null;}
            if($row===null||$row['status']!=='reviewed'||(int)$row['profile_version']!==(int)$row['version_number']){$unresolved++;$details.='<li>'.$this->escape($name).': kein aktueller Prüfstand gespeichert; Thüringer Vorgaben und Kohortenübergänge manuell prüfen.</li>';continue;}$details.='<li>'.$this->escape($name).': geprüfte Profilversion '.(int)$row['profile_version'].' für '.$this->escape((string)$row['cohort_label']).' · Quelle: '.$this->escape((string)$row['source_title']).($row['source_url']?' · <a rel="noopener noreferrer" href="'.htmlspecialchars((string)$row['source_url'],ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8').'">öffnen</a>':'').($row['source_reference']?' · '.$this->escape((string)$row['source_reference']):'').' · geprüft am '.$this->escape((string)$row['source_checked_on']).' durch '.$this->escape((string)$row['reviewer']).'</li>';}$details.='</ul><p class="muted">Dieser Bericht prüft technische Stundenplankonflikte und dokumentiert ausgewählte Quellenstände. Er bildet Thüringer Rechts- und Verwaltungsvorgaben nicht vollständig maschinenlesbar ab und ist kein Rechtskonformitätsnachweis.</p>';
        return ['hard'=>$hard,'soft'=>$soft,'unresolved'=>$unresolved,'details'=>$details];
    }

    public function listPlans(int $schoolId,int $yearId): array
    {
        $stmt=$this->pdo->prepare("SELECT p.id,p.version_number,p.title,p.status,p.rules_review_required,p.created_at,u.username AS creator FROM {$this->prefix}timetable_plans p JOIN {$this->prefix}users u ON u.id=p.created_by WHERE p.school_id=:school_id AND p.school_year_id=:year_id ORDER BY p.version_number DESC");
        $stmt->execute(['school_id'=>$schoolId,'year_id'=>$yearId]);
        return $stmt->fetchAll();
    }

    public function findPlan(int $schoolId,int $planId): ?array
    {
        $stmt=$this->pdo->prepare("SELECT id,school_id,school_year_id,version_number,title,status,rules_review_required FROM {$this->prefix}timetable_plans WHERE school_id=:school_id AND id=:plan_id");
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
        $stmt=$this->pdo->prepare("SELECT l.id,l.class_id,c.class_code,c.grade_level,c.expected_size,c.is_active AS class_active,c.offering_id,o.offering_name,o.status AS offering_status,o.valid_from AS offering_valid_from,o.valid_until AS offering_valid_until,sy.starts_on AS year_starts_on,sy.ends_on AS year_ends_on,l.subject_id,s.subject_code,s.subject_name,s.preferred_room_type_key,s.preferred_room_type_name,s.is_active AS subject_active,l.teacher_user_id,u.username,EXISTS(SELECT 1 FROM {$this->prefix}school_memberships m JOIN {$this->prefix}role_bindings rb ON rb.membership_id=m.id WHERE m.school_id=l.school_id AND m.user_id=l.teacher_user_id AND m.status='active' AND m.valid_from<=CURDATE() AND (m.valid_until IS NULL OR m.valid_until>=CURDATE()) AND rb.role_key='teacher' AND rb.valid_from<=CURDATE() AND (rb.valid_until IS NULL OR rb.valid_until>=CURDATE())) AS teacher_active,l.weekday,l.start_period,l.duration_periods,l.room_id,r.room_code,r.room_name,r.room_type_key AS actual_room_type_key,r.room_type_name AS actual_room_type_name,r.capacity AS room_capacity,r.is_active AS room_active,p.label,p.starts_at,p.ends_at,p.is_active AS period_active,EXISTS(SELECT 1 FROM {$this->prefix}timetable_periods p2 WHERE p2.school_id=l.school_id AND p2.period_number=l.start_period+1 AND p2.is_active=1) AS next_period_active FROM {$this->prefix}timetable_lessons l JOIN {$this->prefix}school_classes c ON c.id=l.class_id JOIN {$this->prefix}school_years sy ON sy.id=c.school_year_id JOIN {$this->prefix}school_offerings o ON o.id=c.offering_id JOIN {$this->prefix}school_subjects s ON s.id=l.subject_id JOIN {$this->prefix}users u ON u.id=l.teacher_user_id LEFT JOIN {$this->prefix}school_rooms r ON r.school_id=l.school_id AND r.id=l.room_id JOIN {$this->prefix}timetable_periods p ON p.school_id=l.school_id AND p.period_number=l.start_period WHERE l.school_id=:school_id AND l.plan_id=:plan_id ORDER BY l.weekday,l.start_period,o.offering_name,c.class_code");
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
        if((int)$plan['rules_review_required']===1)throw new PlanningException('Der Regelprüfstand fehlt oder ist veraltet. Prüfe zuerst die Regelprofile im Prüfbericht.');
        $lessons=$this->listLessons($schoolId,$planId);
        $ruleReport=$this->ruleReport($schoolId,(int)$plan['school_year_id'],$planId,$lessons);
        if($ruleReport['unresolved']>0)throw new PlanningException('Für mindestens eine verwendete Schulform fehlt ein aktuelles, fachlich geprüftes Regelprofil.');
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

    private function escape(string $value): string{return htmlspecialchars($value,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}

    private function audit(int $schoolId,int $actor,string $event): void
    {
        $stmt=$this->pdo->prepare("INSERT INTO {$this->prefix}audit_log (school_id,actor_user_id,subject_user_id,event_key,role_key,created_at) VALUES (:school_id,:actor,NULL,:event,NULL,UTC_TIMESTAMP())");
        $stmt->execute(['school_id'=>$schoolId,'actor'=>$actor,'event'=>$event]);
    }
}
