# ✈ Escales

**A travel journal web app written in plain PHP, with no framework.**
*Carnet de voyage partagé, développé en PHP natif, sans framework.*

![PHP](https://img.shields.io/badge/PHP-8.3%2B-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?logo=mysql&logoColor=white)
![No framework](https://img.shields.io/badge/framework-none-0e7c86)
![Tests](https://img.shields.io/badge/tests-47_passing-2ea44f)

![Home page](docs/screenshots/home.png)

## In short

| | |
|---|---|
| **What it does** | Visitors search cities and read reviews. Members keep a journal of the cities they have visited, with a public profile page |
| **What it shows** | PHP fundamentals without a framework: a router, an MVC structure, PDO, sessions, server-side validation and web security |
| **Why no framework** | To show that I understand what a framework like Symfony does for you (see [RecetteVie](https://github.com/puremax-lab/RecetteVie), my Symfony project) |
| **Status** | Tested with MySQL and SQLite, responsive on phones and desktops, covered by automated tests |

## Features

- **Cities**: search by city or country, filter by country, pagination, page per city with reviews and average rating
- **Journal**: add, edit and delete the cities you have visited, with a date, a 1 to 5 star rating and a review
- **Members**: sign-up, login, logout, public profile with statistics (cities, countries, average rating), profile editing
- **Home page**: key figures, most visited cities, latest reviews

## Screenshots

| Cities | City page |
|---|---|
| ![Cities](docs/screenshots/cities.png) | ![City page](docs/screenshots/city.png) |

| My journal | Form validation |
|---|---|
| ![Journal](docs/screenshots/journal.png) | ![Form](docs/screenshots/form.png) |

| Mobile: home | Mobile: profile |
|---|---|
| ![Mobile home](docs/screenshots/mobile-home.png) | ![Mobile profile](docs/screenshots/mobile-member.png) |

## Technical highlights

**Architecture (written from scratch)**
- **Front controller** ([`public/index.php`](public/index.php)): every request goes through one file.
- **Router** ([`Router`](src/Core/Router.php)) with URL parameters such as `/villes/{id}`, and 404 / 405 answers.
- **MVC**: controllers in [`src/Controller`](src/Controller), SQL in repositories ([`src/Repository`](src/Repository)), HTML in PHP templates ([`templates`](templates)) rendered inside a shared layout.
- **Composer** for PSR-4 autoloading and PHPUnit only: no runtime dependency.

**Security**
- **SQL injection**: every query that contains user input is a PDO prepared statement (the others are fixed SQL with no variables).
- **XSS**: all text coming from users or the database is escaped with `e()` (`htmlspecialchars`) before being printed.
- **CSRF**: a token per session, checked on every POST.
- **Passwords**: hashed with `password_hash`, re-hashed when PHP's algorithm changes, never stored in the session.
- **Login**: session id regenerated after login, generic error message, and a **login throttle** stored in the database (5 failures in 15 minutes for the same pseudo and IP address).
- **Access control**: a member can only edit or delete their own entries; others get a 404.
- Session cookies `HttpOnly` and `SameSite=Lax`, and security headers (`X-Frame-Options`, `X-Content-Type-Options`).

**Data and validation**
- **Server-side validation** ([`Validator`](src/Core/Validator.php)): required fields, lengths, real dates, unique pseudo, rating 1 to 5. The form keeps the typed values and shows an error under each field.
- **Database constraints**: foreign keys, unique keys (one entry per city per member), a `CHECK` on the rating, and journal entries deleted with their member.

**Quality**
- **PHPUnit tests**: unit tests for the router, validator and CSRF, and functional tests that send real requests through the whole app (sign-up, login, throttle, journal, permissions, escaping).
- **GitHub Actions** runs the tests on PHP 8.3 and 8.4 at every push.

## Tech stack

| | |
|---|---|
| Language | PHP 8.3+ (no framework) |
| Database | MySQL or SQLite |
| Front end | HTML and CSS, no library, no JavaScript |
| Tests | PHPUnit 12 |
| CI | GitHub Actions |

## Database

```mermaid
erDiagram
    countries ||--o{ cities : "has"
    cities ||--o{ users : "home of"
    users ||--o{ visits : "writes"
    cities ||--o{ visits : "visited in"

    countries {
        int id PK
        varchar name UK
        char code UK
        varchar nationality
    }
    cities {
        int id PK
        int country_id FK
        varchar name
        boolean is_capital
    }
    users {
        int id PK
        varchar pseudo UK
        varchar first_name
        varchar last_name
        varchar password_hash
        date birth_date
        int city_id FK
        datetime created_at
    }
    visits {
        int id PK
        int user_id FK
        int city_id FK
        date visited_on
        tinyint rating "1 to 5"
        varchar note
        datetime created_at
    }
```

- A member lives in one city, and their nationality comes from that city's country.
- A member has at most one journal entry per city (`UNIQUE (user_id, city_id)`).
- A `login_attempts` table, not linked to the others, stores failed logins for the throttle.

## Run it locally

**Requirements:** PHP 8.3+ with the `mbstring` extension and `pdo_sqlite` or `pdo_mysql`, and [Composer](https://getcomposer.org).

**Quick start with SQLite (no database server needed):**

```bash
git clone https://github.com/puremax-lab/Escales.git
cd Escales
composer install
php bin/setup.php --sqlite
composer serve
```

`setup.php --sqlite` creates the database with the demo data and writes `config/config.local.php`. Open http://127.0.0.1:8000 and log in with **camille** / **password** (other demo accounts: `malik`, `lena`, same password).

**With MySQL:** create an empty `escales` database, copy `config/config.local.php.dist` to `config/config.local.php` with your credentials, then run `php bin/setup.php`.

**Run the tests:**

```bash
composer test
```

## Author

**puremax-lab**: [github.com/puremax-lab](https://github.com/puremax-lab)
