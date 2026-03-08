# AI Assistant Instructions for hrms_laravel

Purpose: Help AI coding agents be immediately productive in this Laravel HRMS repository by describing the app structure, important files, local workflows, and project-specific conventions.

1) Big picture
- Type: Laravel MVC app (PHP) serving Blade views.
- Controllers: primary entry points are in `app/Http/Controllers` (notably `EmployeeController`, `PagesController`, `UserController`). See [app/Http/Controllers](app/Http/Controllers) for examples.
- Models: core models live in `app/Models` (`Employee`, `Department`, `JobTitle`, `User`) and use Eloquent with `$fillable` and simple `belongsTo` relations.
- Views: Blade templates live under `resources/views` (employees views and `dashbord/*` are important).
- Routes: defined in [routes/web.php](routes/web.php). Route names like `employees.index`, `employees.create` are used by controllers and redirects.

2) Project-specific patterns & conventions
- Validation: inline `->validate()` inside controllers (no FormRequest classes). Mirror this style for consistency.
- Language/content: source contains Arabic UI strings and comments; preserve UTF-8 and Arabic phrasing when editing UX text.
- View paths: `PagesController` returns `dashbord.*` views; verify spelling (`dashbord` not `dashboard`).
- Eloquent: models define relations and `$fillable`. When changing DB writes, update `$fillable` appropriately.
- Seeders/migrations: seeders are in `database/seeders` (e.g., `DepartmentSeeder.php` and `JobTitleSeeder.php`) — use them to bootstrap lookup tables.

3) Local developer workflows (quick commands)
- Install PHP deps: `composer install`
- Node assets: `npm install` then `npm run dev` (vite is configured in `vite.config.js`).
- Environment: copy example: `cp .env.example .env` (Windows: `copy .env.example .env`) and set DB credentials (XAMPP MySQL common).
- App key: `php artisan key:generate`
- Migrate + seed: `php artisan migrate --seed` (or `php artisan migrate` then `php artisan db:seed --class=DepartmentSeeder`)
- Serve locally: either configure XAMPP to point to `public/` or run `php artisan serve` for development.
- Tests: run PHPUnit via `vendor/bin/phpunit` on *nix or `vendor\\bin\\phpunit.bat` on Windows. `phpunit.xml` is present.

4) Editing & PR guidance (what agents should do)
- Keep controller-style validation and redirection patterns (see `EmployeeController::store` / `update`).
- When adding routes, register them in [routes/web.php](routes/web.php) and use named routes for redirects: `route('employees.index')`.
- For new DB columns: add a migration under `database/migrations`, update model `$fillable`, and update seeders if needed.
- Preserve Arabic messages and comments unless the user asks for translation.

5) Integration points & external dependencies
- Database: MySQL (XAMPP) is the expected local DB.
- Frontend: Vite + NPM for asset bundling (`vite.config.js`, `resources/js`, `resources/css`).
- Auth: light-weight `Auth::attempt()` used in `PagesController::login` — session-based Laravel auth.

6) Files to inspect for context when asked
- Routes: [routes/web.php](routes/web.php)
- Controllers: [app/Http/Controllers/EmployeeController.php](app/Http/Controllers/EmployeeController.php), [app/Http/Controllers/PagesController.php](app/Http/Controllers/PagesController.php)
- Models: [app/Models/Employee.php](app/Models/Employee.php), [app/Models/Department.php](app/Models/Department.php)
- Seeders: [database/seeders/DepartmentSeeder.php](database/seeders/DepartmentSeeder.php)
- Views: `resources/views/employees/*` and `resources/views/dashbord/*`

7) Useful examples (copy-and-adapt)
- Simple search + eager load (employees list): see `EmployeeController::index` for filtering with `$request->search` and `with(['department','jobTitle'])`.
- Storing validated input: controllers use `$data = $request->validate([...]); Model::create($data);` — reuse this pattern.

8) What NOT to change without confirmation
- Database table names and column names referenced in models and controllers.
- Arabic UX strings and validation messages.
- The `dashbord` view naming/structure unless replacing all usages project-wide.

If anything here is unclear or you want the file expanded with specific coding examples or CI steps, tell me which sections to elaborate.
