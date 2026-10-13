-- ORDISCHOOL_OPTIONAL_USERNAME_ADD_BEGIN
SET @ordi_ddl = IF(
    EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = '{{prefix}}users'
          AND COLUMN_NAME = 'username'
    ),
    'SELECT 1',
    'ALTER TABLE {{prefix}}users ADD COLUMN username VARCHAR(64) CHARACTER SET ascii COLLATE ascii_bin NULL AFTER id'
);
PREPARE ordi_migration_stmt FROM @ordi_ddl;
EXECUTE ordi_migration_stmt;
DEALLOCATE PREPARE ordi_migration_stmt;
-- ORDISCHOOL_OPTIONAL_USERNAME_ADD_END

UPDATE {{prefix}}users
SET username = LOWER(LEFT(SUBSTRING_INDEX(email, '@', 1), 64))
WHERE username IS NULL;

SET @ordi_major = CAST(SUBSTRING_INDEX(VERSION(), '.', 1) AS UNSIGNED);
SET @ordi_minor = CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(VERSION(), '.', 2), '.', -1) AS UNSIGNED);
SET @ordi_patch = CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(VERSION(), '.', 3), '.', -1) AS UNSIGNED);
SET @ordi_drop_clause = IF(
    VERSION() LIKE '%MariaDB%'
        OR @ordi_major > 8
        OR (@ordi_major = 8 AND @ordi_minor > 0)
        OR (@ordi_major = 8 AND @ordi_minor = 0 AND @ordi_patch >= 19),
    'DROP CONSTRAINT',
    'DROP CHECK'
);
SET @ordi_ddl = IF(
    EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
        WHERE CONSTRAINT_SCHEMA = DATABASE()
          AND TABLE_NAME = '{{prefix}}users'
          AND CONSTRAINT_NAME = 'chk_{{prefix}}users_role'
          AND CONSTRAINT_TYPE = 'CHECK'
    ),
    CONCAT('ALTER TABLE {{prefix}}users ', @ordi_drop_clause, ' chk_{{prefix}}users_role'),
    'SELECT 1'
);
PREPARE ordi_migration_stmt FROM @ordi_ddl;
EXECUTE ordi_migration_stmt;
DEALLOCATE PREPARE ordi_migration_stmt;

ALTER TABLE {{prefix}}users
    MODIFY username VARCHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    MODIFY role ENUM('system_admin', 'member') CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'member';

SET @ordi_ddl = IF(
    EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = '{{prefix}}users'
          AND INDEX_NAME = 'uq_{{prefix}}users_username'
    ),
    'SELECT 1',
    'ALTER TABLE {{prefix}}users ADD UNIQUE KEY uq_{{prefix}}users_username (username)'
);
PREPARE ordi_migration_stmt FROM @ordi_ddl;
EXECUTE ordi_migration_stmt;
DEALLOCATE PREPARE ordi_migration_stmt;

CREATE TABLE IF NOT EXISTS {{prefix}}schools (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    code VARCHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    name VARCHAR(190) NOT NULL,
    school_type VARCHAR(40) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    federal_state VARCHAR(100) NOT NULL DEFAULT 'Thüringen',
    timezone VARCHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'Europe/Berlin',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_{{prefix}}schools_code (code),
    CONSTRAINT chk_{{prefix}}schools_active CHECK (is_active IN (0, 1)),
    CONSTRAINT chk_{{prefix}}schools_type CHECK (school_type IN ('gymnasium', 'regelschule', 'grundschule', 'gemeinschaftsschule', 'berufsbildende_schule', 'sonstige'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS {{prefix}}school_years (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    school_id BIGINT UNSIGNED NOT NULL,
    label VARCHAR(32) NOT NULL,
    starts_on DATE NOT NULL,
    ends_on DATE NOT NULL,
    status VARCHAR(16) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'planned',
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_{{prefix}}school_years_label (school_id, label),
    KEY idx_{{prefix}}school_years_dates (school_id, starts_on, ends_on),
    CONSTRAINT fk_{{prefix}}school_years_school FOREIGN KEY (school_id) REFERENCES {{prefix}}schools (id) ON DELETE RESTRICT,
    CONSTRAINT chk_{{prefix}}school_years_dates CHECK (ends_on >= starts_on),
    CONSTRAINT chk_{{prefix}}school_years_status CHECK (status IN ('planned', 'current', 'archived'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS {{prefix}}school_memberships (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    school_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    valid_from DATE NOT NULL,
    valid_until DATE NULL,
    status VARCHAR(16) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_{{prefix}}school_memberships_school_user (school_id, user_id),
    KEY idx_{{prefix}}school_memberships_user (user_id, status),
    CONSTRAINT fk_{{prefix}}school_memberships_school FOREIGN KEY (school_id) REFERENCES {{prefix}}schools (id) ON DELETE RESTRICT,
    CONSTRAINT fk_{{prefix}}school_memberships_user FOREIGN KEY (user_id) REFERENCES {{prefix}}users (id) ON DELETE RESTRICT,
    CONSTRAINT chk_{{prefix}}school_memberships_dates CHECK (valid_until IS NULL OR valid_until >= valid_from),
    CONSTRAINT chk_{{prefix}}school_memberships_status CHECK (status IN ('active', 'inactive'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS {{prefix}}role_bindings (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    membership_id BIGINT UNSIGNED NOT NULL,
    role_key VARCHAR(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    scope_type VARCHAR(16) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'school',
    valid_from DATE NOT NULL,
    valid_until DATE NULL,
    granted_by BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_{{prefix}}role_bindings_membership_role (membership_id, role_key),
    KEY idx_{{prefix}}role_bindings_role_dates (role_key, valid_from, valid_until),
    CONSTRAINT fk_{{prefix}}role_bindings_membership FOREIGN KEY (membership_id) REFERENCES {{prefix}}school_memberships (id) ON DELETE RESTRICT,
    CONSTRAINT fk_{{prefix}}role_bindings_granted_by FOREIGN KEY (granted_by) REFERENCES {{prefix}}users (id) ON DELETE RESTRICT,
    CONSTRAINT chk_{{prefix}}role_bindings_role CHECK (role_key IN ('school_admin', 'planner', 'teacher', 'class_leader', 'subject_lead', 'office_staff', 'student')),
    CONSTRAINT chk_{{prefix}}role_bindings_scope CHECK (scope_type = 'school'),
    CONSTRAINT chk_{{prefix}}role_bindings_dates CHECK (valid_until IS NULL OR valid_until >= valid_from)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS {{prefix}}audit_log (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    school_id BIGINT UNSIGNED NULL,
    actor_user_id BIGINT UNSIGNED NOT NULL,
    subject_user_id BIGINT UNSIGNED NULL,
    event_key VARCHAR(48) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    role_key VARCHAR(32) CHARACTER SET ascii COLLATE ascii_bin NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_{{prefix}}audit_log_school_time (school_id, created_at),
    KEY idx_{{prefix}}audit_log_actor_time (actor_user_id, created_at),
    CONSTRAINT fk_{{prefix}}audit_log_school FOREIGN KEY (school_id) REFERENCES {{prefix}}schools (id) ON DELETE RESTRICT,
    CONSTRAINT fk_{{prefix}}audit_log_actor FOREIGN KEY (actor_user_id) REFERENCES {{prefix}}users (id) ON DELETE RESTRICT,
    CONSTRAINT fk_{{prefix}}audit_log_subject FOREIGN KEY (subject_user_id) REFERENCES {{prefix}}users (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
