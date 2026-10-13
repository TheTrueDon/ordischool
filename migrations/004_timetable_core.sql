CREATE TABLE IF NOT EXISTS {{prefix}}school_offerings (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    school_id BIGINT UNSIGNED NOT NULL,
    offering_key VARCHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    offering_name VARCHAR(140) NOT NULL,
    valid_from DATE NOT NULL,
    valid_until DATE NULL,
    status VARCHAR(16) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_{{prefix}}school_offerings_key (school_id, offering_key),
    UNIQUE KEY uq_{{prefix}}school_offerings_school_id (school_id, id),
    KEY idx_{{prefix}}school_offerings_validity (school_id, status, valid_from, valid_until),
    CONSTRAINT fk_{{prefix}}school_offerings_school FOREIGN KEY (school_id) REFERENCES {{prefix}}schools (id) ON DELETE RESTRICT,
    CONSTRAINT chk_{{prefix}}school_offerings_dates CHECK (valid_until IS NULL OR valid_until >= valid_from),
    CONSTRAINT chk_{{prefix}}school_offerings_status CHECK (status IN ('active', 'inactive'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO {{prefix}}school_offerings (school_id, offering_key, offering_name, valid_from, valid_until, status, created_at)
SELECT id, school_type,
       CASE school_type
           WHEN 'gymnasium' THEN 'Gymnasium'
           WHEN 'regelschule' THEN 'Regelschule'
           WHEN 'grundschule' THEN 'Grundschule'
           WHEN 'gemeinschaftsschule' THEN 'Gemeinschaftsschule'
           WHEN 'berufsbildende_schule' THEN 'Berufsbildende Schule'
           ELSE 'Sonstige'
       END,
       '2000-01-01', NULL, 'active', UTC_TIMESTAMP()
FROM {{prefix}}schools
ON DUPLICATE KEY UPDATE offering_name = VALUES(offering_name);

CREATE TABLE IF NOT EXISTS {{prefix}}school_classes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    school_id BIGINT UNSIGNED NOT NULL,
    school_year_id BIGINT UNSIGNED NOT NULL,
    offering_id BIGINT UNSIGNED NOT NULL,
    class_code VARCHAR(64) NOT NULL,
    grade_level VARCHAR(32) NOT NULL,
    group_type VARCHAR(16) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'class',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_{{prefix}}school_classes_year_offering_code (school_year_id, offering_id, class_code),
    UNIQUE KEY uq_{{prefix}}school_classes_school_id (school_id, id),
    KEY idx_{{prefix}}school_classes_offering (offering_id, school_year_id, is_active),
    CONSTRAINT fk_{{prefix}}school_classes_school FOREIGN KEY (school_id) REFERENCES {{prefix}}schools (id) ON DELETE RESTRICT,
    CONSTRAINT fk_{{prefix}}school_classes_year FOREIGN KEY (school_year_id) REFERENCES {{prefix}}school_years (id) ON DELETE RESTRICT,
    CONSTRAINT fk_{{prefix}}school_classes_offering FOREIGN KEY (school_id, offering_id) REFERENCES {{prefix}}school_offerings (school_id, id) ON DELETE RESTRICT,
    CONSTRAINT chk_{{prefix}}school_classes_type CHECK (group_type IN ('class', 'course', 'group')),
    CONSTRAINT chk_{{prefix}}school_classes_active CHECK (is_active IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS {{prefix}}school_subjects (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    school_id BIGINT UNSIGNED NOT NULL,
    subject_code VARCHAR(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    subject_name VARCHAR(120) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_{{prefix}}school_subjects_code (school_id, subject_code),
    UNIQUE KEY uq_{{prefix}}school_subjects_school_id (school_id, id),
    CONSTRAINT fk_{{prefix}}school_subjects_school FOREIGN KEY (school_id) REFERENCES {{prefix}}schools (id) ON DELETE RESTRICT,
    CONSTRAINT chk_{{prefix}}school_subjects_active CHECK (is_active IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS {{prefix}}timetable_periods (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    school_id BIGINT UNSIGNED NOT NULL,
    period_number SMALLINT UNSIGNED NOT NULL,
    label VARCHAR(40) NOT NULL,
    starts_at TIME NOT NULL,
    ends_at TIME NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_{{prefix}}timetable_periods_number (school_id, period_number),
    UNIQUE KEY uq_{{prefix}}timetable_periods_school_id (school_id, id),
    CONSTRAINT fk_{{prefix}}timetable_periods_school FOREIGN KEY (school_id) REFERENCES {{prefix}}schools (id) ON DELETE RESTRICT,
    CONSTRAINT chk_{{prefix}}timetable_periods_order CHECK (ends_at > starts_at),
    CONSTRAINT chk_{{prefix}}timetable_periods_active CHECK (is_active IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS {{prefix}}timetable_plans (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    school_id BIGINT UNSIGNED NOT NULL,
    school_year_id BIGINT UNSIGNED NOT NULL,
    version_number INT UNSIGNED NOT NULL,
    title VARCHAR(140) NOT NULL,
    status VARCHAR(16) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'draft',
    based_on_plan_id BIGINT UNSIGNED NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    approved_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL,
    published_at DATETIME NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_{{prefix}}timetable_plans_year_version (school_year_id, version_number),
    UNIQUE KEY uq_{{prefix}}timetable_plans_school_id (school_id, id),
    KEY idx_{{prefix}}timetable_plans_status (school_id, school_year_id, status),
    CONSTRAINT fk_{{prefix}}timetable_plans_school FOREIGN KEY (school_id) REFERENCES {{prefix}}schools (id) ON DELETE RESTRICT,
    CONSTRAINT fk_{{prefix}}timetable_plans_year FOREIGN KEY (school_year_id) REFERENCES {{prefix}}school_years (id) ON DELETE RESTRICT,
    CONSTRAINT fk_{{prefix}}timetable_plans_based_on FOREIGN KEY (based_on_plan_id) REFERENCES {{prefix}}timetable_plans (id) ON DELETE RESTRICT,
    CONSTRAINT fk_{{prefix}}timetable_plans_creator FOREIGN KEY (created_by) REFERENCES {{prefix}}users (id) ON DELETE RESTRICT,
    CONSTRAINT fk_{{prefix}}timetable_plans_approver FOREIGN KEY (approved_by) REFERENCES {{prefix}}users (id) ON DELETE RESTRICT,
    CONSTRAINT chk_{{prefix}}timetable_plans_status CHECK (status IN ('draft', 'published', 'superseded'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS {{prefix}}timetable_lessons (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    school_id BIGINT UNSIGNED NOT NULL,
    plan_id BIGINT UNSIGNED NOT NULL,
    class_id BIGINT UNSIGNED NOT NULL,
    subject_id BIGINT UNSIGNED NOT NULL,
    teacher_user_id BIGINT UNSIGNED NOT NULL,
    weekday TINYINT UNSIGNED NOT NULL,
    start_period SMALLINT UNSIGNED NOT NULL,
    duration_periods TINYINT UNSIGNED NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_{{prefix}}timetable_lessons_plan_time (plan_id, weekday, start_period),
    KEY idx_{{prefix}}timetable_lessons_teacher_time (school_id, teacher_user_id, weekday, start_period),
    KEY idx_{{prefix}}timetable_lessons_class_time (class_id, weekday, start_period),
    CONSTRAINT fk_{{prefix}}timetable_lessons_plan FOREIGN KEY (school_id, plan_id) REFERENCES {{prefix}}timetable_plans (school_id, id) ON DELETE RESTRICT,
    CONSTRAINT fk_{{prefix}}timetable_lessons_class FOREIGN KEY (school_id, class_id) REFERENCES {{prefix}}school_classes (school_id, id) ON DELETE RESTRICT,
    CONSTRAINT fk_{{prefix}}timetable_lessons_subject FOREIGN KEY (school_id, subject_id) REFERENCES {{prefix}}school_subjects (school_id, id) ON DELETE RESTRICT,
    CONSTRAINT fk_{{prefix}}timetable_lessons_teacher FOREIGN KEY (teacher_user_id) REFERENCES {{prefix}}users (id) ON DELETE RESTRICT,
    CONSTRAINT chk_{{prefix}}timetable_lessons_weekday CHECK (weekday BETWEEN 1 AND 5),
    CONSTRAINT chk_{{prefix}}timetable_lessons_duration CHECK (duration_periods IN (1, 2))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
