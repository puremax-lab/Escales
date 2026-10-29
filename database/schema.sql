-- Escales: database schema (MySQL 8 / MariaDB 10.6+)

CREATE TABLE countries (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(60) NOT NULL,
    code CHAR(2) NOT NULL,
    nationality VARCHAR(40) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_country_name (name),
    UNIQUE KEY uniq_country_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cities (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    country_id INT UNSIGNED NOT NULL,
    name VARCHAR(80) NOT NULL,
    is_capital TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_city_per_country (country_id, name),
    CONSTRAINT fk_city_country FOREIGN KEY (country_id) REFERENCES countries (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    pseudo VARCHAR(30) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    birth_date DATE NOT NULL,
    city_id INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_user_pseudo (pseudo),
    CONSTRAINT fk_user_city FOREIGN KEY (city_id) REFERENCES cities (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- One entry per city in each member's journal.
CREATE TABLE visits (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    city_id INT UNSIGNED NOT NULL,
    visited_on DATE NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,
    note VARCHAR(500) NOT NULL DEFAULT '',
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_visit_user_city (user_id, city_id),
    KEY idx_visit_city (city_id),
    CONSTRAINT fk_visit_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_visit_city FOREIGN KEY (city_id) REFERENCES cities (id),
    CONSTRAINT chk_visit_rating CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Failed logins, to slow down password guessing.
CREATE TABLE login_attempts (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    attempt_key CHAR(64) NOT NULL,
    attempted_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_attempt_key (attempt_key, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
