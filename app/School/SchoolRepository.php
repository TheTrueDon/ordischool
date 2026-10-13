<?php
declare(strict_types=1);

namespace OrdiSchool\School;

use PDO;
use RuntimeException;

final class SchoolRepository
{
    public function __construct(private readonly PDO $pdo, private readonly string $prefix)
    {
    }

    public function listSchools(): array
    {
        return $this->pdo->query("SELECT id, code, name, school_type, federal_state, timezone, is_active FROM {$this->prefix}schools ORDER BY name")
            ->fetchAll();
    }

    public function schoolsForUser(int $userId, string $globalRole = 'member'): array
    {
        if($globalRole==='system_admin')return $this->listSchools();
        $statement = $this->pdo->prepare("SELECT DISTINCT s.id, s.code, s.name, s.school_type, s.federal_state, s.timezone, s.is_active FROM {$this->prefix}school_memberships m JOIN {$this->prefix}schools s ON s.id = m.school_id JOIN {$this->prefix}role_bindings rb ON rb.membership_id = m.id JOIN {$this->prefix}school_role_permissions rp ON rp.school_id=m.school_id AND rp.role_key=rb.role_key WHERE m.user_id = :user_id AND m.status = 'active' AND m.valid_from <= CURDATE() AND (m.valid_until IS NULL OR m.valid_until >= CURDATE()) AND rp.permission_key='school.manage' AND rp.is_allowed=1 AND rb.valid_from <= CURDATE() AND (rb.valid_until IS NULL OR rb.valid_until >= CURDATE()) AND s.is_active = 1 ORDER BY s.name");
        $statement->execute(['user_id' => $userId]);
        return $statement->fetchAll();
    }

    public function findSchool(int $schoolId): ?array
    {
        $statement = $this->pdo->prepare("SELECT id, code, name, school_type, federal_state, timezone, is_active FROM {$this->prefix}schools WHERE id = :id LIMIT 1");
        $statement->execute(['id' => $schoolId]);
        $school = $statement->fetch();
        return is_array($school) ? $school : null;
    }

    public function createSchool(string $code, string $name, string $type, string $state, int $actorId, array $offeringKeys = []): int
    {
        $this->pdo->beginTransaction();
        try {
            $statement = $this->pdo->prepare("INSERT INTO {$this->prefix}schools (code, name, school_type, federal_state, timezone, is_active, created_at, updated_at) VALUES (:code, :name, :type, :state, 'Europe/Berlin', 1, UTC_TIMESTAMP(), UTC_TIMESTAMP())");
            $statement->execute(['code' => $code, 'name' => $name, 'type' => $type, 'state' => $state]);
            $schoolId = (int) $this->pdo->lastInsertId();
            $offeringNames = ['gymnasium'=>'Gymnasium','regelschule'=>'Regelschule','grundschule'=>'Grundschule','gemeinschaftsschule'=>'Gemeinschaftsschule','berufsbildende_schule'=>'Berufsbildende Schule','sonstige'=>'Sonstige'];
            $offering = $this->pdo->prepare("INSERT INTO {$this->prefix}school_offerings (school_id, offering_key, offering_name, valid_from, status, created_at) VALUES (:school_id, :offering_key, :offering_name, '1900-01-01', 'active', UTC_TIMESTAMP())");
            $keys = $offeringKeys === [] ? [$type] : array_values(array_unique($offeringKeys));
            foreach ($keys as $offeringKey) {
                $offering->execute(['school_id'=>$schoolId,'offering_key'=>$offeringKey,'offering_name'=>$offeringNames[$offeringKey] ?? $offeringKey]);
            }
            $this->pdo->prepare("INSERT INTO {$this->prefix}school_buildings (school_id,building_code,building_name,is_active,created_at) VALUES (:school_id,'main','Hauptgebäude',1,UTC_TIMESTAMP())")->execute(['school_id'=>$schoolId]);
            $this->pdo->prepare("INSERT INTO {$this->prefix}school_memberships (school_id,user_id,valid_from,status,created_at) VALUES (:school_id,:user_id,CURDATE(),'active',UTC_TIMESTAMP())")->execute(['school_id'=>$schoolId,'user_id'=>$actorId]);
            $membershipId=(int)$this->pdo->lastInsertId();
            $this->pdo->prepare("INSERT INTO {$this->prefix}role_bindings (membership_id,role_key,scope_id,scope_type,valid_from,granted_by,created_at) VALUES (:membership_id,'school_admin',0,'school',CURDATE(),:actor,UTC_TIMESTAMP())")->execute(['membership_id'=>$membershipId,'actor'=>$actorId]);
            $roleNames=['school_admin'=>'Schuladministration','planner'=>'Planung','teacher'=>'Lehrkraft','class_leader'=>'Klassenleitung','subject_lead'=>'Fachleitung','office_staff'=>'Sekretariat','caretaker'=>'Hausmeister','social_worker'=>'Sozialarbeit','student'=>'Schüler/in'];
            $roleInsert=$this->pdo->prepare("INSERT INTO {$this->prefix}school_role_definitions (school_id,role_key,role_name,is_system,is_active,created_at) VALUES (:school_id,:role_key,:role_name,1,1,UTC_TIMESTAMP())");
            foreach($roleNames as $key=>$label)$roleInsert->execute(['school_id'=>$schoolId,'role_key'=>$key,'role_name'=>$label]);
            $all=$this->pdo->query("SELECT permission_key FROM {$this->prefix}school_permission_catalog")->fetchAll(PDO::FETCH_COLUMN);
            $grant=$this->pdo->prepare("INSERT INTO {$this->prefix}school_role_permissions (school_id,role_key,permission_key,is_allowed) VALUES (:school_id,:role_key,:permission,1)");
            foreach($all as $permission)$grant->execute(['school_id'=>$schoolId,'role_key'=>'school_admin','permission'=>$permission]);
            foreach(['planner'=>['planning.view','planning.edit'],'teacher'=>['planning.view'],'class_leader'=>['planning.view'],'subject_lead'=>['planning.view']] as $key=>$permissions)foreach($permissions as $permission)$grant->execute(['school_id'=>$schoolId,'role_key'=>$key,'permission'=>$permission]);
            $this->pdo->prepare("INSERT INTO {$this->prefix}school_people (school_id,user_id,person_kind,first_name,last_name,is_active,created_at) SELECT :school_id,u.id,'staff',COALESCE(u.first_name,''),COALESCE(u.last_name,u.username),u.is_active,UTC_TIMESTAMP() FROM {$this->prefix}users u WHERE u.id=:user_id")->execute(['school_id'=>$schoolId,'user_id'=>$actorId]);
            $this->audit($schoolId, $actorId, null, 'school_created', null);
            $this->pdo->commit();
            return $schoolId;
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function offeringNames(int $schoolId): array
    {
        $statement=$this->pdo->prepare("SELECT offering_name FROM {$this->prefix}school_offerings WHERE school_id=:school_id AND status='active' AND (valid_until IS NULL OR valid_until>=CURDATE()) ORDER BY offering_name");
        $statement->execute(['school_id'=>$schoolId]);
        return array_column($statement->fetchAll(), 'offering_name');
    }

    public function listYears(int $schoolId): array
    {
        $statement = $this->pdo->prepare("SELECT id, label, starts_on, ends_on, status FROM {$this->prefix}school_years WHERE school_id = :school_id ORDER BY starts_on DESC");
        $statement->execute(['school_id' => $schoolId]);
        return $statement->fetchAll();
    }

    public function createSchoolYear(int $schoolId, string $label, string $start, string $end, string $status, int $actorId): void
    {
        $this->pdo->beginTransaction();
        try {
            if ($status === 'current') {
                $statement = $this->pdo->prepare("UPDATE {$this->prefix}school_years SET status = 'archived' WHERE school_id = :school_id AND status = 'current'");
                $statement->execute(['school_id' => $schoolId]);
            }
            $statement = $this->pdo->prepare("INSERT INTO {$this->prefix}school_years (school_id, label, starts_on, ends_on, status, created_at) VALUES (:school_id, :label, :starts_on, :ends_on, :status, UTC_TIMESTAMP())");
            $statement->execute(['school_id' => $schoolId, 'label' => $label, 'starts_on' => $start, 'ends_on' => $end, 'status' => $status]);
            $this->audit($schoolId, $actorId, null, 'school_year_created', null);
            $this->pdo->commit();
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function rolesForUser(int $userId): array
    {
        $statement = $this->pdo->prepare("SELECT s.id AS school_id, s.name AS school_name, m.id AS membership_id, rb.role_key, rb.scope_id, rb.valid_from, rb.valid_until FROM {$this->prefix}school_memberships m JOIN {$this->prefix}schools s ON s.id = m.school_id JOIN {$this->prefix}role_bindings rb ON rb.membership_id = m.id WHERE m.user_id = :user_id AND m.status = 'active' AND m.valid_from <= CURDATE() AND (m.valid_until IS NULL OR m.valid_until >= CURDATE()) AND s.is_active = 1 AND rb.valid_from <= CURDATE() AND (rb.valid_until IS NULL OR rb.valid_until >= CURDATE()) ORDER BY s.name, rb.role_key");
        $statement->execute(['user_id' => $userId]);
        return $statement->fetchAll();
    }

    public function canManageSchool(int $userId,string $globalRole,int $schoolId): bool
    {
        return $this->hasPermission($userId,$globalRole,$schoolId,'school.manage');
    }

    public function hasPermission(int $userId,string $globalRole,int $schoolId,string $permission): bool
    {
        if($globalRole==='system_admin')return true;
        $stmt=$this->pdo->prepare("SELECT 1 FROM {$this->prefix}school_memberships m JOIN {$this->prefix}role_bindings rb ON rb.membership_id=m.id JOIN {$this->prefix}school_role_permissions rp ON rp.school_id=m.school_id AND rp.role_key=rb.role_key WHERE m.user_id=:user_id AND m.school_id=:school_id AND m.status='active' AND m.valid_from<=CURDATE() AND (m.valid_until IS NULL OR m.valid_until>=CURDATE()) AND rb.valid_from<=CURDATE() AND (rb.valid_until IS NULL OR rb.valid_until>=CURDATE()) AND rb.scope_id=0 AND rp.permission_key=:permission AND rp.is_allowed=1 LIMIT 1");
        $stmt->execute(['user_id'=>$userId,'school_id'=>$schoolId,'permission'=>$permission]);return $stmt->fetchColumn()!==false;
    }

    public function roleDefinitions(int $schoolId): array
    {
        $stmt=$this->pdo->prepare("SELECT role_key,role_name,description,is_system,is_active FROM {$this->prefix}school_role_definitions WHERE school_id=:school_id AND is_active=1 ORDER BY role_name");$stmt->execute(['school_id'=>$schoolId]);return $stmt->fetchAll();
    }

    public function permissionCatalog(): array
    {
        return $this->pdo->query("SELECT permission_key,permission_name,module_key,description FROM {$this->prefix}school_permission_catalog ORDER BY module_key,permission_name")->fetchAll();
    }

    public function rolePermissionMap(int $schoolId): array
    {
        $stmt=$this->pdo->prepare("SELECT role_key,permission_key,is_allowed FROM {$this->prefix}school_role_permissions WHERE school_id=:school_id");$stmt->execute(['school_id'=>$schoolId]);$map=[];foreach($stmt->fetchAll() as $row)$map[$row['role_key']][$row['permission_key']]=(int)$row['is_allowed']===1;return $map;
    }

    public function saveRolePermissions(int $schoolId,string $roleKey,array $permissions,int $actorId): void
    {
        $this->pdo->beginTransaction();try{$check=$this->pdo->prepare("SELECT is_system FROM {$this->prefix}school_role_definitions WHERE school_id=:school_id AND role_key=:role_key AND is_active=1 FOR UPDATE");$check->execute(['school_id'=>$schoolId,'role_key'=>$roleKey]);if($check->fetchColumn()===false)throw new RuntimeException('Die Rolle wurde nicht gefunden.');$valid=array_column($this->permissionCatalog(),'permission_key');if(array_diff($permissions,$valid)!==[])throw new RuntimeException('Unbekannte Berechtigung.');$this->pdo->prepare("DELETE FROM {$this->prefix}school_role_permissions WHERE school_id=:school_id AND role_key=:role_key")->execute(['school_id'=>$schoolId,'role_key'=>$roleKey]);$insert=$this->pdo->prepare("INSERT INTO {$this->prefix}school_role_permissions (school_id,role_key,permission_key,is_allowed) VALUES (:school_id,:role_key,:permission,1)");foreach(array_unique($permissions) as $permission)$insert->execute(['school_id'=>$schoolId,'role_key'=>$roleKey,'permission'=>$permission]);$this->audit($schoolId,$actorId,null,'role_permissions_changed',$roleKey);$this->pdo->commit();}catch(\Throwable $e){if($this->pdo->inTransaction())$this->pdo->rollBack();throw $e;}
    }

    public function createRole(int $schoolId,string $key,string $label,string $description,int $actorId): void
    {
        $stmt=$this->pdo->prepare("INSERT INTO {$this->prefix}school_role_definitions (school_id,role_key,role_name,description,is_system,is_active,created_at) VALUES (:school_id,:role_key,:label,:description,0,1,UTC_TIMESTAMP())");$stmt->execute(['school_id'=>$schoolId,'role_key'=>$key,'label'=>$label,'description'=>$description]);$this->audit($schoolId,$actorId,null,'role_created',$key);
    }

    public function listMembers(int $schoolId): array
    {
        $stmt=$this->pdo->prepare("SELECT u.id AS user_id,u.username,u.first_name,u.last_name,u.is_active AS user_active,m.id AS membership_id,m.status AS membership_status,m.valid_from,m.valid_until,GROUP_CONCAT(CONCAT(rb.role_key,':',rb.scope_id) ORDER BY rb.role_key,rb.scope_id SEPARATOR ',') AS roles FROM {$this->prefix}school_memberships m JOIN {$this->prefix}users u ON u.id=m.user_id LEFT JOIN {$this->prefix}role_bindings rb ON rb.membership_id=m.id AND rb.valid_from<=CURDATE() AND (rb.valid_until IS NULL OR rb.valid_until>=CURDATE()) WHERE m.school_id=:school_id GROUP BY u.id,u.username,u.first_name,u.last_name,u.is_active,m.id,m.status,m.valid_from,m.valid_until ORDER BY u.last_name,u.first_name,u.username");$stmt->execute(['school_id'=>$schoolId]);return $stmt->fetchAll();
    }

    public function createMember(string $username,string $passwordHash,int $schoolId,string $firstName,string $lastName,array $assignments,int $actorId): int
    {
        $this->pdo->beginTransaction();try{$email=$username.'@ordischool.invalid';$stmt=$this->pdo->prepare("INSERT INTO {$this->prefix}users (username,first_name,last_name,email,password_hash,role,is_active,created_at) VALUES (:username,:first_name,:last_name,:email,:hash,'member',1,UTC_TIMESTAMP())");$stmt->execute(['username'=>$username,'first_name'=>$firstName,'last_name'=>$lastName,'email'=>$email,'hash'=>$passwordHash]);$userId=(int)$this->pdo->lastInsertId();$stmt=$this->pdo->prepare("INSERT INTO {$this->prefix}school_memberships (school_id,user_id,valid_from,status,created_at) VALUES (:school_id,:user_id,CURDATE(),'active',UTC_TIMESTAMP())");$stmt->execute(['school_id'=>$schoolId,'user_id'=>$userId]);$membershipId=(int)$this->pdo->lastInsertId();$stmt=$this->pdo->prepare("INSERT INTO {$this->prefix}school_people (school_id,user_id,person_kind,first_name,last_name,is_active,created_at) VALUES (:school_id,:user_id,'staff',:first_name,:last_name,1,UTC_TIMESTAMP())");$stmt->execute(['school_id'=>$schoolId,'user_id'=>$userId,'first_name'=>$firstName,'last_name'=>$lastName]);$insert=$this->pdo->prepare("INSERT INTO {$this->prefix}role_bindings (membership_id,role_key,scope_id,scope_type,valid_from,granted_by,created_at) VALUES (:membership_id,:role_key,:scope_id,'school',CURDATE(),:actor,UTC_TIMESTAMP())");foreach($assignments as $assignment){$role=(string)($assignment['role_key']??'');$scope=(int)($assignment['scope_id']??0);$valid=$this->pdo->prepare("SELECT 1 FROM {$this->prefix}school_role_definitions WHERE school_id=:school_id AND role_key=:role_key AND is_active=1");$valid->execute(['school_id'=>$schoolId,'role_key'=>$role]);if($valid->fetchColumn()===false)throw new RuntimeException('Eine ausgewählte Rolle ist ungültig.');if($scope>0){$off=$this->pdo->prepare("SELECT 1 FROM {$this->prefix}school_offerings WHERE school_id=:school_id AND id=:id AND status='active'");$off->execute(['school_id'=>$schoolId,'id'=>$scope]);if($off->fetchColumn()===false)throw new RuntimeException('Eine ausgewählte Schulform gehört nicht zu dieser Schule.');}$insert->execute(['membership_id'=>$membershipId,'role_key'=>$role,'scope_id'=>$scope,'actor'=>$actorId]);}$this->audit($schoolId,$actorId,$userId,'membership_created',null);$this->pdo->commit();return $userId;}catch(\Throwable $e){if($this->pdo->inTransaction())$this->pdo->rollBack();throw $e;}
    }

    public function setMemberRoles(int $schoolId,int $membershipId,array $assignments,int $actorId): void
    {
        $this->pdo->beginTransaction();try{$check=$this->pdo->prepare("SELECT id FROM {$this->prefix}school_memberships WHERE school_id=:school_id AND id=:id FOR UPDATE");$check->execute(['school_id'=>$schoolId,'id'=>$membershipId]);if($check->fetchColumn()===false)throw new RuntimeException('Mitgliedschaft nicht gefunden.');$this->pdo->prepare("DELETE FROM {$this->prefix}role_bindings WHERE membership_id=:id")->execute(['id'=>$membershipId]);$insert=$this->pdo->prepare("INSERT INTO {$this->prefix}role_bindings (membership_id,role_key,scope_id,scope_type,valid_from,granted_by,created_at) VALUES (:membership_id,:role_key,:scope_id,'school',CURDATE(),:actor,UTC_TIMESTAMP())");foreach($assignments as $assignment){$role=(string)($assignment['role_key']??'');$scope=(int)($assignment['scope_id']??0);$valid=$this->pdo->prepare("SELECT 1 FROM {$this->prefix}school_role_definitions WHERE school_id=:school_id AND role_key=:role_key AND is_active=1");$valid->execute(['school_id'=>$schoolId,'role_key'=>$role]);if($valid->fetchColumn()===false)throw new RuntimeException('Eine ausgewählte Rolle ist ungültig.');if($scope>0){$off=$this->pdo->prepare("SELECT 1 FROM {$this->prefix}school_offerings WHERE school_id=:school_id AND id=:id AND status='active'");$off->execute(['school_id'=>$schoolId,'id'=>$scope]);if($off->fetchColumn()===false)throw new RuntimeException('Eine ausgewählte Schulform gehört nicht zu dieser Schule.');}$insert->execute(['membership_id'=>$membershipId,'role_key'=>$role,'scope_id'=>$scope,'actor'=>$actorId]);}$this->audit($schoolId,$actorId,null,'member_roles_changed',null);$this->pdo->commit();}catch(\Throwable $e){if($this->pdo->inTransaction())$this->pdo->rollBack();throw $e;}
    }

    public function createPerson(int $schoolId,string $kind,string $first,string $last,?int $classId,int $actorId): void
    {
        $stmt=$this->pdo->prepare("INSERT INTO {$this->prefix}school_people (school_id,person_kind,first_name,last_name,class_id,is_active,created_at) VALUES (:school_id,:kind,:first,:last,:class_id,1,UTC_TIMESTAMP())");$stmt->execute(['school_id'=>$schoolId,'kind'=>$kind,'first'=>$first,'last'=>$last,'class_id'=>$classId]);$this->audit($schoolId,$actorId,null,$kind==='student'?'student_created':'person_created',null);
    }

    public function listPeople(int $schoolId,string $kind): array
    {
        $stmt=$this->pdo->prepare("SELECT p.id,p.user_id,p.class_id,p.first_name,p.last_name,p.is_active,c.class_code,u.username FROM {$this->prefix}school_people p LEFT JOIN {$this->prefix}school_classes c ON c.school_id=p.school_id AND c.id=p.class_id LEFT JOIN {$this->prefix}users u ON u.id=p.user_id WHERE p.school_id=:school_id AND p.person_kind=:kind AND p.is_active=1 ORDER BY p.last_name,p.first_name");$stmt->execute(['school_id'=>$schoolId,'kind'=>$kind]);return $stmt->fetchAll();
    }

    public function setMembershipActive(int $membershipId, bool $active, int $actorId, int $schoolId): void
    {
        $this->pdo->beginTransaction();
        try {
            $statement = $this->pdo->prepare("SELECT user_id, status FROM {$this->prefix}school_memberships WHERE id = :id AND school_id = :school_id FOR UPDATE");
            $statement->execute(['id' => $membershipId, 'school_id' => $schoolId]);
            $membership = $statement->fetch();
            if (!is_array($membership)) {
                throw new RuntimeException('Die Mitgliedschaft wurde nicht gefunden.');
            }
            $newStatus = $active ? 'active' : 'inactive';
            if ($membership['status'] === $newStatus) {
                $this->pdo->commit();
                return;
            }
            $statement = $this->pdo->prepare("UPDATE {$this->prefix}school_memberships SET status = :status WHERE id = :id AND school_id = :school_id");
            $statement->execute(['status' => $newStatus, 'id' => $membershipId, 'school_id' => $schoolId]);
            $this->audit($schoolId, $actorId, (int) $membership['user_id'], $active ? 'membership_activated' : 'membership_deactivated', null);
            $this->pdo->commit();
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    private function audit(int $schoolId, int $actorId, ?int $subjectId, string $event, ?string $role): void
    {
        $statement = $this->pdo->prepare("INSERT INTO {$this->prefix}audit_log (school_id, actor_user_id, subject_user_id, event_key, role_key, created_at) VALUES (:school_id, :actor, :subject, :event, :role, UTC_TIMESTAMP())");
        $statement->execute(['school_id' => $schoolId, 'actor' => $actorId, 'subject' => $subjectId, 'event' => $event, 'role' => $role]);
    }
}
