-- Opens the journal to every city in the world (SQLite).
-- Two towns can share a name in one country, so the unique (country, name) key becomes a unique
-- OpenStreetMap reference. SQLite cannot drop a constraint: the table is rebuilt, ids are kept.
PRAGMA foreign_keys = OFF;
BEGIN;
CREATE TABLE cities_new (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    country_id INTEGER NOT NULL REFERENCES countries (id),
    name TEXT NOT NULL,
    region TEXT NOT NULL DEFAULT '',
    is_capital INTEGER NOT NULL DEFAULT 0,
    place_ref TEXT UNIQUE
);
INSERT INTO cities_new (id, country_id, name, is_capital) SELECT id, country_id, name, is_capital FROM cities;
DROP TABLE cities;
ALTER TABLE cities_new RENAME TO cities;
CREATE INDEX idx_city_country ON cities (country_id, name);
COMMIT;
PRAGMA foreign_keys = ON;
