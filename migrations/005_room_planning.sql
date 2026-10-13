CREATE TABLE IF NOT EXISTS {{prefix}}school_rooms (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    school_id BIGINT UNSIGNED NOT NULL,
    room_code VARCHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    room_name VARCHAR(140) NOT NULL,
    room_type_key VARCHAR(40) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    room_type_name VARCHAR(100) NOT NULL,
    capacity SMALLINT UNSIGNED NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_{{prefix}}school_rooms_code (school_id, room_code),
    UNIQUE KEY uq_{{prefix}}school_rooms_school_id (school_id, id),
    KEY idx_{{prefix}}school_rooms_type (school_id, room_type_key, is_active),
    CONSTRAINT fk_{{prefix}}school_rooms_school FOREIGN KEY (school_id) REFERENCES {{prefix}}schools (id) ON DELETE RESTRICT,
    CONSTRAINT chk_{{prefix}}school_rooms_capacity CHECK (capacity IS NULL OR capacity > 0),
    CONSTRAINT chk_{{prefix}}school_rooms_active CHECK (is_active IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SET @ordi_ddl = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='{{prefix}}school_classes' AND COLUMN_NAME='expected_size')=0, 'ALTER TABLE {{prefix}}school_classes ADD COLUMN expected_size SMALLINT UNSIGNED NULL', 'SELECT 1');
PREPARE ordi_stmt FROM @ordi_ddl;
EXECUTE ordi_stmt;
DEALLOCATE PREPARE ordi_stmt;
SET @ordi_ddl = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='{{prefix}}school_subjects' AND COLUMN_NAME='preferred_room_type_key')=0, 'ALTER TABLE {{prefix}}school_subjects ADD COLUMN preferred_room_type_key VARCHAR(40) CHARACTER SET ascii COLLATE ascii_bin NULL', 'SELECT 1');
PREPARE ordi_stmt FROM @ordi_ddl;
EXECUTE ordi_stmt;
DEALLOCATE PREPARE ordi_stmt;
SET @ordi_ddl = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='{{prefix}}school_subjects' AND COLUMN_NAME='preferred_room_type_name')=0, 'ALTER TABLE {{prefix}}school_subjects ADD COLUMN preferred_room_type_name VARCHAR(100) NULL', 'SELECT 1');
PREPARE ordi_stmt FROM @ordi_ddl;
EXECUTE ordi_stmt;
DEALLOCATE PREPARE ordi_stmt;
SET @ordi_ddl = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='{{prefix}}timetable_lessons' AND COLUMN_NAME='room_id')=0, 'ALTER TABLE {{prefix}}timetable_lessons ADD COLUMN room_id BIGINT UNSIGNED NULL', 'SELECT 1');
PREPARE ordi_stmt FROM @ordi_ddl;
EXECUTE ordi_stmt;
DEALLOCATE PREPARE ordi_stmt;
SET @ordi_ddl = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='{{prefix}}timetable_lessons' AND INDEX_NAME='idx_{{prefix}}timetable_lessons_room_time')=0, 'ALTER TABLE {{prefix}}timetable_lessons ADD KEY idx_{{prefix}}timetable_lessons_room_time (school_id, room_id, weekday, start_period)', 'SELECT 1');
PREPARE ordi_stmt FROM @ordi_ddl;
EXECUTE ordi_stmt;
DEALLOCATE PREPARE ordi_stmt;
SET @ordi_ddl = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME='{{prefix}}timetable_lessons' AND CONSTRAINT_NAME='fk_{{prefix}}timetable_lessons_room')=0, 'ALTER TABLE {{prefix}}timetable_lessons ADD CONSTRAINT fk_{{prefix}}timetable_lessons_room FOREIGN KEY (school_id, room_id) REFERENCES {{prefix}}school_rooms (school_id, id) ON DELETE RESTRICT', 'SELECT 1');
PREPARE ordi_stmt FROM @ordi_ddl;
EXECUTE ordi_stmt;
DEALLOCATE PREPARE ordi_stmt;
