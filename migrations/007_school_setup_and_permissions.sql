CREATE TABLE IF NOT EXISTS {{prefix}}school_buildings (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    school_id BIGINT UNSIGNED NOT NULL,
    building_code VARCHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    building_name VARCHAR(140) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_{{prefix}}school_buildings_code (school_id, building_code),
    UNIQUE KEY uq_{{prefix}}school_buildings_school_id (school_id, id),
    CONSTRAINT fk_{{prefix}}school_buildings_school FOREIGN KEY (school_id) REFERENCES {{prefix}}schools (id) ON DELETE RESTRICT,
    CONSTRAINT chk_{{prefix}}school_buildings_active CHECK (is_active IN (0,1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO {{prefix}}school_buildings (school_id,building_code,building_name,is_active,created_at)
SELECT id,'main','Hauptgebäude',1,UTC_TIMESTAMP() FROM {{prefix}}schools
ON DUPLICATE KEY UPDATE building_name=building_name;

SET @ordi_ddl = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='{{prefix}}school_rooms' AND COLUMN_NAME='building_id')=0, 'ALTER TABLE {{prefix}}school_rooms ADD COLUMN building_id BIGINT UNSIGNED NULL AFTER school_id', 'SELECT 1');
PREPARE ordi_stmt FROM @ordi_ddl;
EXECUTE ordi_stmt;
DEALLOCATE PREPARE ordi_stmt;
UPDATE {{prefix}}school_rooms r JOIN {{prefix}}school_buildings b ON b.school_id=r.school_id AND b.building_code='main' SET r.building_id=b.id WHERE r.building_id IS NULL;
ALTER TABLE {{prefix}}school_rooms MODIFY building_id BIGINT UNSIGNED NOT NULL;
SET @ordi_ddl = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='{{prefix}}school_rooms' AND INDEX_NAME='idx_{{prefix}}school_rooms_building')=0, 'ALTER TABLE {{prefix}}school_rooms ADD KEY idx_{{prefix}}school_rooms_building (school_id,building_id,is_active)', 'SELECT 1');
PREPARE ordi_stmt FROM @ordi_ddl;
EXECUTE ordi_stmt;
DEALLOCATE PREPARE ordi_stmt;
SET @ordi_ddl = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME='{{prefix}}school_rooms' AND CONSTRAINT_NAME='fk_{{prefix}}school_rooms_building')=0, 'ALTER TABLE {{prefix}}school_rooms ADD CONSTRAINT fk_{{prefix}}school_rooms_building FOREIGN KEY (school_id,building_id) REFERENCES {{prefix}}school_buildings (school_id,id) ON DELETE RESTRICT', 'SELECT 1');
PREPARE ordi_stmt FROM @ordi_ddl;
EXECUTE ordi_stmt;
DEALLOCATE PREPARE ordi_stmt;

SET @ordi_ddl = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='{{prefix}}users' AND COLUMN_NAME='first_name')=0, 'ALTER TABLE {{prefix}}users ADD COLUMN first_name VARCHAR(100) NULL AFTER username', 'SELECT 1');
PREPARE ordi_stmt FROM @ordi_ddl;
EXECUTE ordi_stmt;
DEALLOCATE PREPARE ordi_stmt;
SET @ordi_ddl = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='{{prefix}}users' AND COLUMN_NAME='last_name')=0, 'ALTER TABLE {{prefix}}users ADD COLUMN last_name VARCHAR(100) NULL AFTER first_name', 'SELECT 1');
PREPARE ordi_stmt FROM @ordi_ddl;
EXECUTE ordi_stmt;
DEALLOCATE PREPARE ordi_stmt;

CREATE TABLE IF NOT EXISTS {{prefix}}school_people (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    school_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    class_id BIGINT UNSIGNED NULL,
    person_kind VARCHAR(16) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_{{prefix}}school_people_school_id (school_id,id),
    UNIQUE KEY uq_{{prefix}}school_people_user (school_id,user_id),
    KEY idx_{{prefix}}school_people_class (school_id,class_id,person_kind,is_active),
    CONSTRAINT fk_{{prefix}}school_people_school FOREIGN KEY (school_id) REFERENCES {{prefix}}schools (id) ON DELETE RESTRICT,
    CONSTRAINT fk_{{prefix}}school_people_user FOREIGN KEY (user_id) REFERENCES {{prefix}}users (id) ON DELETE RESTRICT,
    CONSTRAINT fk_{{prefix}}school_people_class FOREIGN KEY (school_id,class_id) REFERENCES {{prefix}}school_classes (school_id,id) ON DELETE RESTRICT,
    CONSTRAINT chk_{{prefix}}school_people_kind CHECK (person_kind IN ('staff','student')),
    CONSTRAINT chk_{{prefix}}school_people_active CHECK (is_active IN (0,1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS {{prefix}}school_role_definitions (
    school_id BIGINT UNSIGNED NOT NULL,
    role_key VARCHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    role_name VARCHAR(120) NOT NULL,
    description VARCHAR(500) NULL,
    is_system TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (school_id,role_key),
    CONSTRAINT fk_{{prefix}}school_role_definitions_school FOREIGN KEY (school_id) REFERENCES {{prefix}}schools (id) ON DELETE RESTRICT,
    CONSTRAINT chk_{{prefix}}school_role_definitions_system CHECK (is_system IN (0,1)),
    CONSTRAINT chk_{{prefix}}school_role_definitions_active CHECK (is_active IN (0,1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS {{prefix}}school_permission_catalog (
    permission_key VARCHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    permission_name VARCHAR(140) NOT NULL,
    module_key VARCHAR(40) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    description VARCHAR(500) NOT NULL,
    PRIMARY KEY (permission_key),
    KEY idx_{{prefix}}school_permission_module (module_key,permission_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO {{prefix}}school_permission_catalog (permission_key,permission_name,module_key,description) VALUES
('school.manage','Schule verwalten','administration','Schulstammdaten und Schuljahre verwalten'),
('members.manage','Personen verwalten','administration','Personal, Konten und Mitgliedschaften verwalten'),
('roles.manage','Rollen und Rechte verwalten','administration','Rollen anlegen, Berechtigungen vergeben und Rollen zuordnen'),
('buildings.manage','Gebäude und Räume verwalten','setup','Gebäude und Raumstammdaten pflegen'),
('subjects.manage','Fächer verwalten','setup','Schulfächer und Fachraumwünsche pflegen'),
('classes.manage','Klassen verwalten','setup','Klassen, Klassenleitung und Stellvertretung verwalten'),
('students.manage','Schüler verwalten','setup','Schüler anlegen und Klassen zuordnen'),
('planning.view','Stundenpläne ansehen','planning','Stundenplan und Prüfbericht ansehen'),
('planning.edit','Stundenpläne bearbeiten','planning','Entwürfe und Unterrichtseinträge bearbeiten'),
('planning.configure','Planungsgrundlagen verwalten','planning','Planungsgrundlagen und Regelprofile pflegen'),
('planning.approve','Stundenpläne freigeben','planning','Prüfstände speichern und Stundenpläne freigeben')
ON DUPLICATE KEY UPDATE permission_name=VALUES(permission_name),module_key=VALUES(module_key),description=VALUES(description);

INSERT INTO {{prefix}}school_role_definitions (school_id,role_key,role_name,is_system,is_active,created_at)
SELECT s.id,r.role_key,r.role_name,1,1,UTC_TIMESTAMP() FROM {{prefix}}schools s
CROSS JOIN (SELECT 'school_admin' role_key,'Schuladministration' role_name UNION ALL SELECT 'planner','Planung' UNION ALL SELECT 'teacher','Lehrkraft' UNION ALL SELECT 'class_leader','Klassenleitung' UNION ALL SELECT 'subject_lead','Fachleitung' UNION ALL SELECT 'office_staff','Sekretariat' UNION ALL SELECT 'caretaker','Hausmeister' UNION ALL SELECT 'social_worker','Sozialarbeit' UNION ALL SELECT 'student','Schüler/in') r
ON DUPLICATE KEY UPDATE role_name=VALUES(role_name);

CREATE TABLE IF NOT EXISTS {{prefix}}school_role_permissions (
    school_id BIGINT UNSIGNED NOT NULL,
    role_key VARCHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    permission_key VARCHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    is_allowed TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (school_id,role_key,permission_key),
    CONSTRAINT fk_{{prefix}}role_permissions_role FOREIGN KEY (school_id,role_key) REFERENCES {{prefix}}school_role_definitions (school_id,role_key) ON DELETE RESTRICT,
    CONSTRAINT fk_{{prefix}}role_permissions_permission FOREIGN KEY (permission_key) REFERENCES {{prefix}}school_permission_catalog (permission_key) ON DELETE RESTRICT,
    CONSTRAINT chk_{{prefix}}role_permissions_allowed CHECK (is_allowed IN (0,1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO {{prefix}}school_role_permissions (school_id,role_key,permission_key,is_allowed)
SELECT r.school_id,r.role_key,p.permission_key,1 FROM {{prefix}}school_role_definitions r CROSS JOIN {{prefix}}school_permission_catalog p WHERE r.role_key='school_admin'
ON DUPLICATE KEY UPDATE is_allowed=is_allowed;
INSERT INTO {{prefix}}school_role_permissions (school_id,role_key,permission_key,is_allowed)
SELECT r.school_id,r.role_key,p.permission_key,1 FROM {{prefix}}school_role_definitions r JOIN {{prefix}}school_permission_catalog p ON p.permission_key IN ('planning.view','planning.edit') WHERE r.role_key='planner'
ON DUPLICATE KEY UPDATE is_allowed=is_allowed;
INSERT INTO {{prefix}}school_role_permissions (school_id,role_key,permission_key,is_allowed)
SELECT r.school_id,r.role_key,p.permission_key,1 FROM {{prefix}}school_role_definitions r JOIN {{prefix}}school_permission_catalog p ON p.permission_key IN ('planning.view') WHERE r.role_key IN ('teacher','class_leader','subject_lead')
ON DUPLICATE KEY UPDATE is_allowed=is_allowed;

SET @ordi_ddl = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='{{prefix}}role_bindings' AND COLUMN_NAME='scope_id')=0, 'ALTER TABLE {{prefix}}role_bindings ADD COLUMN scope_id BIGINT UNSIGNED NOT NULL DEFAULT 0 AFTER role_key', 'SELECT 1');
PREPARE ordi_stmt FROM @ordi_ddl;
EXECUTE ordi_stmt;
DEALLOCATE PREPARE ordi_stmt;
SET @ordi_major = CAST(SUBSTRING_INDEX(VERSION(),'.',1) AS UNSIGNED);
SET @ordi_minor = CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(VERSION(),'.',2),'.',-1) AS UNSIGNED);
SET @ordi_patch = CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(VERSION(),'.',3),'.',-1) AS UNSIGNED);
SET @ordi_drop_clause = IF(VERSION() LIKE '%MariaDB%' OR @ordi_major>8 OR (@ordi_major=8 AND @ordi_minor>0) OR (@ordi_major=8 AND @ordi_minor=0 AND @ordi_patch>=19),'DROP CONSTRAINT','DROP CHECK');
SET @ordi_ddl = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME='{{prefix}}role_bindings' AND CONSTRAINT_NAME='chk_{{prefix}}role_bindings_role' AND CONSTRAINT_TYPE='CHECK')>0, CONCAT('ALTER TABLE {{prefix}}role_bindings ',@ordi_drop_clause,' chk_{{prefix}}role_bindings_role'), 'SELECT 1');
PREPARE ordi_stmt FROM @ordi_ddl;
EXECUTE ordi_stmt;
DEALLOCATE PREPARE ordi_stmt;
ALTER TABLE {{prefix}}role_bindings MODIFY role_key VARCHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL;
SET @ordi_ddl = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='{{prefix}}role_bindings' AND INDEX_NAME='uq_{{prefix}}role_bindings_membership_role')>0, 'ALTER TABLE {{prefix}}role_bindings DROP INDEX uq_{{prefix}}role_bindings_membership_role', 'SELECT 1');
PREPARE ordi_stmt FROM @ordi_ddl;
EXECUTE ordi_stmt;
DEALLOCATE PREPARE ordi_stmt;
SET @ordi_ddl = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='{{prefix}}role_bindings' AND INDEX_NAME='uq_{{prefix}}role_bindings_membership_role_scope')=0, 'ALTER TABLE {{prefix}}role_bindings ADD UNIQUE KEY uq_{{prefix}}role_bindings_membership_role_scope (membership_id,role_key,scope_id)', 'SELECT 1');
PREPARE ordi_stmt FROM @ordi_ddl;
EXECUTE ordi_stmt;
DEALLOCATE PREPARE ordi_stmt;



SET @ordi_ddl = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='{{prefix}}school_classes' AND COLUMN_NAME='class_leader_user_id')=0, 'ALTER TABLE {{prefix}}school_classes ADD COLUMN class_leader_user_id BIGINT UNSIGNED NULL AFTER expected_size', 'SELECT 1');
PREPARE ordi_stmt FROM @ordi_ddl;
EXECUTE ordi_stmt;
DEALLOCATE PREPARE ordi_stmt;
SET @ordi_ddl = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='{{prefix}}school_classes' AND COLUMN_NAME='deputy_class_leader_user_id')=0, 'ALTER TABLE {{prefix}}school_classes ADD COLUMN deputy_class_leader_user_id BIGINT UNSIGNED NULL AFTER class_leader_user_id', 'SELECT 1');
PREPARE ordi_stmt FROM @ordi_ddl;
EXECUTE ordi_stmt;
DEALLOCATE PREPARE ordi_stmt;

INSERT INTO {{prefix}}school_people (school_id,user_id,person_kind,first_name,last_name,is_active,created_at)
SELECT m.school_id,m.user_id,'staff',COALESCE(u.first_name,''),COALESCE(u.last_name,u.username),u.is_active,UTC_TIMESTAMP()
FROM {{prefix}}school_memberships m JOIN {{prefix}}users u ON u.id=m.user_id
ON DUPLICATE KEY UPDATE user_id=VALUES(user_id);
