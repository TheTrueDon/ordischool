CREATE TABLE {{prefix}}auth_throttle (
    bucket_key CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    attempts SMALLINT UNSIGNED NOT NULL,
    window_started_at DATETIME NOT NULL,
    PRIMARY KEY (bucket_key),
    KEY idx_{{prefix}}auth_throttle_window (window_started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
