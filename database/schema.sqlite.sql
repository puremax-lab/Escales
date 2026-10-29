-- Escales: same schema as schema.sql, for SQLite (quick start and tests).

CREATE TABLE countries (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    code TEXT NOT NULL UNIQUE,
    nationality TEXT NOT NULL
);

CREATE TABLE cities (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    country_id INTEGER NOT NULL REFERENCES countries (id),
    name TEXT NOT NULL,
    is_capital INTEGER NOT NULL DEFAULT 0,
    UNIQUE (country_id, name)
);

CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    pseudo TEXT NOT NULL UNIQUE,
    first_name TEXT NOT NULL,
    last_name TEXT NOT NULL,
    password_hash TEXT NOT NULL,
    birth_date TEXT NOT NULL,
    city_id INTEGER NOT NULL REFERENCES cities (id),
    created_at TEXT NOT NULL
);

CREATE TABLE visits (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL REFERENCES users (id) ON DELETE CASCADE,
    city_id INTEGER NOT NULL REFERENCES cities (id),
    visited_on TEXT NOT NULL,
    rating INTEGER NOT NULL CHECK (rating BETWEEN 1 AND 5),
    note TEXT NOT NULL DEFAULT '',
    created_at TEXT NOT NULL,
    UNIQUE (user_id, city_id)
);
CREATE INDEX idx_visit_city ON visits (city_id);

CREATE TABLE login_attempts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    attempt_key TEXT NOT NULL,
    attempted_at TEXT NOT NULL
);
CREATE INDEX idx_attempt_key ON login_attempts (attempt_key, attempted_at);
