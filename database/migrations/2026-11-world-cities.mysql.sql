-- Opens the journal to every city in the world (MySQL / MariaDB).
ALTER TABLE cities
    ADD COLUMN region VARCHAR(80) NOT NULL DEFAULT '' AFTER name,
    ADD COLUMN place_ref VARCHAR(24) NULL AFTER is_capital,
    ADD UNIQUE KEY uniq_city_place (place_ref),
    ADD KEY idx_city_country (country_id, name),
    DROP INDEX uniq_city_per_country;
