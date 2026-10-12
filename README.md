# Research Repository

A Laravel app for managing research proposals. Proponents submit proposals, and reviewers approve or disapprove them.

## Requirements

- PHP 8.4.12 (8.3 or newer)
- Laravel 13.35.0
- MySQL 8.4.3
- Composer 2

## Setup

```sh
git clone https://github.com/siuolnad/research_repository.git
cd research_repository
composer install
cp .env.example .env
php artisan key:generate
```

Create an empty MySQL database named `research_repository` and check the `DB_*` values in `.env`. Then run:

```sh
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Open <http://localhost:8000>.

## Test accounts

Both accounts use the password `password`.

| Role | Email |
|---|---|
| Proponent | `proponent@example.com` |
| Reviewer | `reviewer@example.com` |

## Tests

```sh
php artisan test
```
