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

    public function schoolsForUser(int $userId): array
    {
        $statement = $this->pdo->prepare("SELECT DISTINCT s.id, s.code, s.name, s.school_type, s.federal_state, s.timezone, s.is_active FROM {$this->prefix}school_memberships m JOIN {$this->prefix}schools s ON s.id = m.school_id JOIN {$this->prefix}role_bindings rb ON rb.membership_id = m.id WHERE m.user_id = :user_id AND m.status = 'active' AND m.valid_from <= CURDATE() AND (m.valid_until IS NULL OR m.valid_until >= CURDATE()) AND rb.role_key = 'school_admin' AND rb.valid_from <= CURDATE() AND (rb.valid_until IS NULL OR rb.valid_until >= CURDATE()) AND s.is_active = 1 ORDER BY s.name");
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
        $statement = $this->pdo->prepare("SELECT s.id AS school_id, s.name AS school_name, m.id AS membership_id, rb.role_key, rb.valid_from, rb.valid_until FROM {$this->prefix}school_memberships m JOIN {$this->prefix}schools s ON s.id = m.school_id JOIN {$this->prefix}role_bindings rb ON rb.membership_id = m.id WHERE m.user_id = :user_id AND m.status = 'active' AND m.valid_from <= CURDATE() AND (m.valid_until IS NULL OR m.valid_until >= CURDATE()) AND s.is_active = 1 AND rb.valid_from <= CURDATE() AND (rb.valid_until IS NULL OR rb.valid_until >= CURDATE()) ORDER BY s.name, rb.role_key");
        $statement->execute(['user_id' => $userId]);
        return $statement->fetchAll();
    }

    public function canManageSchool(int $userId, string $globalRole, int $schoolId): bool
    {
        if ($globalRole === 'system_admin') {
            return true;
        }
        $statement = $this->pdo->prepare("SELECT 1 FROM {$this->prefix}school_memberships m JOIN {$this->prefix}role_bindings rb ON rb.membership_id = m.id WHERE m.user_id = :user_id AND m.school_id = :school_id AND m.status = 'active' AND m.valid_from <= CURDATE() AND (m.valid_until IS NULL OR m.valid_until >= CURDATE()) AND rb.role_key = 'school_admin' AND rb.valid_from <= CURDATE() AND (rb.valid_until IS NULL OR rb.valid_until >= CURDATE()) LIMIT 1");
        $statement->execute(['user_id' => $userId, 'school_id' => $schoolId]);
        return $statement->fetchColumn() !== false;
    }

    public function listMembers(int $schoolId): array
    {
        $statement = $this->pdo->prepare("SELECT u.id AS user_id, u.username, u.is_active AS user_active, m.id AS membership_id, m.status AS membership_status, m.valid_from, m.valid_until, GROUP_CONCAT(rb.role_key ORDER BY rb.role_key SEPARATOR ',') AS roles FROM {$this->prefix}school_memberships m JOIN {$this->prefix}users u ON u.id = m.user_id LEFT JOIN {$this->prefix}role_bindings rb ON rb.membership_id = m.id AND rb.valid_from <= CURDATE() AND (rb.valid_until IS NULL OR rb.valid_until >= CURDATE()) WHERE m.school_id = :school_id GROUP BY u.id, u.username, u.is_active, m.id, m.status, m.valid_from, m.valid_until ORDER BY u.username");
        $statement->execute(['school_id' => $schoolId]);
        return $statement->fetchAll();
    }

    public function createMember(string $username, string $passwordHash, int $schoolId, string $roleKey, int $actorId): int
    {
        $this->pdo->beginTransaction();
        try {
            $email = $username . '@ordischool.invalid';
            $statement = $this->pdo->prepare("INSERT INTO {$this->prefix}users (username, email, password_hash, role, is_active, created_at) VALUES (:username, :email, :password_hash, 'member', 1, UTC_TIMESTAMP())");
            $statement->execute(['username' => $username, 'email' => $email, 'password_hash' => $passwordHash]);
            $userId = (int) $this->pdo->lastInsertId();
            $statement = $this->pdo->prepare("INSERT INTO {$this->prefix}school_memberships (school_id, user_id, valid_from, status, created_at) VALUES (:school_id, :user_id, CURDATE(), 'active', UTC_TIMESTAMP())");
            $statement->execute(['school_id' => $schoolId, 'user_id' => $userId]);
            $membershipId = (int) $this->pdo->lastInsertId();
            $statement = $this->pdo->prepare("INSERT INTO {$this->prefix}role_bindings (membership_id, role_key, scope_type, valid_from, granted_by, created_at) VALUES (:membership_id, :role_key, 'school', CURDATE(), :granted_by, UTC_TIMESTAMP())");
            $statement->execute(['membership_id' => $membershipId, 'role_key' => $roleKey, 'granted_by' => $actorId]);
            $this->audit($schoolId, $actorId, $userId, 'membership_created', $roleKey);
            $this->pdo->commit();
            return $userId;
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
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
