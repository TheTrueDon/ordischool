CREATE TABLE IF NOT EXISTS {{prefix}}planning_rule_profiles (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    school_id BIGINT UNSIGNED NOT NULL,
    school_year_id BIGINT UNSIGNED NOT NULL,
    offering_id BIGINT UNSIGNED NOT NULL,
    version_number INT UNSIGNED NOT NULL,
    cohort_label VARCHAR(120) NOT NULL,
    source_title VARCHAR(240) NOT NULL,
    source_url VARCHAR(1000) NULL,
    source_reference VARCHAR(500) NULL,
    source_checked_on DATE NOT NULL,
    status VARCHAR(16) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT 'draft',
    created_by BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL,
    reviewed_by BIGINT UNSIGNED NULL,
    reviewed_at DATETIME NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_{{prefix}}rule_profiles_school_id (school_id, id),
    UNIQUE KEY uq_{{prefix}}rule_profiles_version (school_year_id, offering_id, version_number),
    KEY idx_{{prefix}}rule_profiles_scope (school_id, school_year_id, offering_id, status),
    CONSTRAINT fk_{{prefix}}rule_profiles_school FOREIGN KEY (school_id) REFERENCES {{prefix}}schools (id) ON DELETE RESTRICT,
    CONSTRAINT fk_{{prefix}}rule_profiles_year FOREIGN KEY (school_year_id) REFERENCES {{prefix}}school_years (id) ON DELETE RESTRICT,
    CONSTRAINT fk_{{prefix}}rule_profiles_offering FOREIGN KEY (school_id, offering_id) REFERENCES {{prefix}}school_offerings (school_id, id) ON DELETE RESTRICT,
    CONSTRAINT fk_{{prefix}}rule_profiles_creator FOREIGN KEY (created_by) REFERENCES {{prefix}}users (id) ON DELETE RESTRICT,
    CONSTRAINT fk_{{prefix}}rule_profiles_reviewer FOREIGN KEY (reviewed_by) REFERENCES {{prefix}}users (id) ON DELETE RESTRICT,
    CONSTRAINT chk_{{prefix}}rule_profiles_status CHECK (status IN ('draft', 'reviewed', 'retired'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS {{prefix}}timetable_plan_rule_reviews (
    school_id BIGINT UNSIGNED NOT NULL,
    plan_id BIGINT UNSIGNED NOT NULL,
    offering_id BIGINT UNSIGNED NOT NULL,
    rule_profile_id BIGINT UNSIGNED NOT NULL,
    profile_version INT UNSIGNED NOT NULL,
    checked_by BIGINT UNSIGNED NOT NULL,
    checked_at DATETIME NOT NULL,
    PRIMARY KEY (plan_id, offering_id),
    KEY idx_{{prefix}}plan_rule_review_profile (rule_profile_id),
    CONSTRAINT fk_{{prefix}}plan_rule_review_plan FOREIGN KEY (school_id, plan_id) REFERENCES {{prefix}}timetable_plans (school_id, id) ON DELETE RESTRICT,
    CONSTRAINT fk_{{prefix}}plan_rule_review_profile FOREIGN KEY (school_id, rule_profile_id) REFERENCES {{prefix}}planning_rule_profiles (school_id, id) ON DELETE RESTRICT,
    CONSTRAINT fk_{{prefix}}plan_rule_review_offering FOREIGN KEY (school_id, offering_id) REFERENCES {{prefix}}school_offerings (school_id, id) ON DELETE RESTRICT,
    CONSTRAINT fk_{{prefix}}plan_rule_review_user FOREIGN KEY (checked_by) REFERENCES {{prefix}}users (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @ordi_ddl = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='{{prefix}}timetable_plans' AND COLUMN_NAME='rules_review_required')=0, 'ALTER TABLE {{prefix}}timetable_plans ADD COLUMN rules_review_required TINYINT(1) NOT NULL DEFAULT 1', 'SELECT 1');
PREPARE ordi_stmt FROM @ordi_ddl;
EXECUTE ordi_stmt;
DEALLOCATE PREPARE ordi_stmt;
