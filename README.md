# Research Repository

A Laravel app for managing research proposals. Proponents submit proposals with objectives, a research area, a schedule, a budget and supporting files. Reviewers approve or disapprove them, and every change is recorded in a history log.

## Requirements

| Tool | Version used in development |
|---|---|
| PHP | 8.4.12 (minimum 8.3) |
| Laravel | 13.35.0 |
| MySQL | 8.4.3 |
| Composer | 2.x |

Required PHP extensions: `fileinfo`, `mbstring`, `openssl`, `pdo_mysql`, `pdo_sqlite` (tests), `ctype`, `json`, `tokenizer`, `xml`.

## Setup

1. Clone the repository and install dependencies:

   ```sh
   git clone https://github.com/siuolnad/research_repository.git
   cd research_repository
   composer install
   ```

2. Create the environment file and app key:

   ```sh
   cp .env.example .env
   php artisan key:generate
   ```

3. Create an empty MySQL database named `research_repository`, then check the `DB_*` values in `.env`. The defaults are:

   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=research_repository
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. Create the tables and load the demo data (two accounts and 25 sample proposals):

   ```sh
   php artisan migrate --seed
   ```

5. Create the storage link:

   ```sh
   php artisan storage:link
   ```

6. Start the app:

   ```sh
   php artisan serve
   ```

   Then open <http://localhost:8000>. If you serve the project through Apache (for example Laragon), open the `public` folder's URL instead.

## Login credentials

The seeder creates two test accounts. Both use the password `password`.

| Role | Email | Password |
|---|---|---|
| Proponent | `proponent@example.com` | `password` |
| Reviewer | `reviewer@example.com` | `password` |

Change or remove these accounts before using the app anywhere other than your own machine. There is no registration page; create further accounts with `php artisan tinker`.

## Roles

- **Proponent** adds proposals and edits their own while they are New. A proponent sees only their own proposals and cannot approve or disapprove.
- **Reviewer** sees every proposal, can add and edit, and is the only role that can approve or disapprove. Disapproving requires remarks.
- Once a proposal is approved or disapproved, its details are locked. Supporting files can still be added or removed.

## Supporting files

- PDF, DOC, DOCX and XLSX only, 10 MB each, up to 10 files per upload.
- Files are saved in `storage/app/private` and downloaded through the app, so only people allowed to see a proposal can download its files.
- PHP's `upload_max_filesize` and `post_max_size` must be above 10 MB. If uploads fail on Windows with "Path must not be empty", set `upload_tmp_dir` in `php.ini` to a normal folder.

## Tests

```sh
php artisan test
```

The tests run on an in-memory SQLite database (set in `phpunit.xml`), so they never touch your MySQL data. This needs the `pdo_sqlite` PHP extension.
