# Attendance System v2 - Clean MVC Architecture

## Overview
This document describes the refactored MVC architecture for the attendance management system.

## Directory Structure

```
attendance-v2/
├── app/
│   ├── Controllers/          # HTTP request handlers
│   ├── Models/               # Business logic & data access
│   ├── Views/                # Templates (HTML/PHP)
│   │   ├── layouts/          # Shared layouts
│   │   │   ├── main.php      # Main layout with nav
│   │   │   └── calendar.php  # Calendar layout
│   │   ├── auth/             # Login/logout views
│   │   ├── shift/            # Shift management
│   │   ├── user/             # User management
│   │   ├── pay/              # Payroll
│   │   └── partials/         # Reusable components
│   └── Helpers/              # Utility functions
│
├── core/                     # Framework core
│   ├── Application.php       # Bootstrap & config loading
│   ├── Controller.php        # Base controller
│   ├── Model.php            # Base model with CRUD
│   ├── View.php             # View rendering
│   ├── Router.php           # URL routing
│   ├── Database.php         # PDO singleton
│   ├── Session.php          # Session management
│   └── Response.php         # HTTP responses
│
├── config/                   # Configuration files
│   ├── app.php              # App settings
│   ├── database_new.php     # DB config (new)
│   ├── database.php         # DB config (legacy)
│   └── session.php          # Session settings
│
├── public/                   # Web root
│   ├── index.php            # Front controller
│   ├── assets/
│   │   ├── css/             # Stylesheets
│   │   ├── js/              # JavaScript
│   │   └── images/          # Images
│   └── .htaccess            # URL rewriting
│
├── routes/                   # Route definitions
│   └── web.php              # All web routes
│
├── legacy/                   # Old code (backup)
│   ├── root/                # Files from root dir
│   └── api/                 # Old API endpoints
│
├── tests/                    # Testing & debugging
│   ├── test_data.php
│   ├── test_api.php
│   └── fix_shift_data.php
│
└── docs/                     # Documentation
    ├── ARCHITECTURE.md       # This file
    ├── API.md               # API documentation
    └── MIGRATION.md         # Migration guide
```

## Core Components

### 1. Database (`core/Database.php`)
Singleton pattern for PDO connection:
```php
$users = Database::fetchAll("SELECT * FROM users WHERE role = ?", ['employee']);
$user = Database::fetchOne("SELECT * FROM users WHERE id = ?", [5]);
```

### 2. Session (`core/Session.php`)
Centralized session management:
```php
Session::setUser($userArray);
$userId = Session::userId();
$isAdmin = Session::isAdmin();
```

### 3. Response (`core/Response.php`)
HTTP response helpers:
```php
Response::json(['status' => 'success', 'data' => $shifts]);
Response::error('Invalid input', 400);
Response::redirect('/menu');
```

### 4. Base Model (`app/Models/BaseModel.php`)
```php
class BaseModel {
    protected string $table;
    protected string $primaryKey = 'id';

    public function find($id): ?array
    public function all(): array
    public function create(array $data): int
    public function update($id, array $data): bool
    public function delete($id): bool
    public function where(string $column, $value): array
}
```

### 5. Model Example (`app/Models/Shift.php`)
```php
class Shift extends BaseModel {
    protected string $table = 'shifts';

    public function getByUser(int $userId): array
    public function getByDate(string $date): array
    public function getByDateRange(string $start, string $end): array
    public function getWithUserInfo(int $shiftId): ?array
    public function deleteGroup(string $repeatId): int
}
```

## Request Flow

```
HTTP Request
    ↓
public/index.php (Front Controller)
    ↓
core/Application::run()
    ↓
core/Router::dispatch()
    ↓
app/Controllers/*Controller::method()
    ↓
app/Models/*Model::query()
    ↓
core/Database::execute()
    ↓
app/Views/*.php (rendered)
    ↓
HTTP Response
```

## API Endpoints (New Approach)

Instead of separate `/api/*.php` files, use controller methods:

```php
// Old way: /api/get_all_shifts.php
// New way: /shift/api/all

class ShiftController {
    public function apiAll() {
        $shifts = $this->shiftModel->getAllWithUsers();
        Response::success($shifts);
    }
}
```

## Session Management

All session handling centralized in `core/Session.php`:
```php
// Before: Duplicate session_start() everywhere
session_name('attendance_session');
session_set_cookie_params([...]);
session_start();

// After: Single configuration
Session::start(); // Auto-configured
```

## View Layouts

### Main Layout (`app/Views/layouts/main.php`)
```php
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'Attendance System' ?></title>
    <link rel="stylesheet" href="/attendance-v2/public/assets/css/main.css">
</head>
<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>
    <main>
        <?= $content ?>
    </main>
    <?php include __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
```

### Using Layouts
```php
// In controller:
$this->view('shift/calendar', [
    'shifts' => $shifts
], 'calendar'); // Use calendar layout
```

## Migration Strategy

### Phase 1: Infrastructure (Completed)
- ✅ Created core/Database.php
- ✅ Created core/Session.php
- ✅ Created core/Response.php
- ✅ Created config files

### Phase 2: Models (Next)
1. Create BaseModel with CRUD
2. Create Shift model
3. Create User model
4. Create Pay models

### Phase 3: Controller Refactoring
1. Update controllers to use models
2. Remove direct PDO usage
3. Add API methods
4. Use Response helpers

### Phase 4: Views
1. Create layouts
2. Extract CSS to files
3. Extract JS to files
4. Remove duplication

### Phase 5: Cleanup
1. Move legacy files
2. Update routes
3. Remove old API folder
4. Create documentation

## Benefits

1. **Maintainability**: Clear separation of concerns
2. **Reusability**: Shared components (layouts, models)
3. **Testability**: Isolated business logic
4. **Security**: Centralized session & DB access
5. **Performance**: Connection pooling, caching potential
6. **Scalability**: Easy to add features

## Next Steps

1. Finish model layer implementation
2. Refactor controllers one by one
3. Create view layouts
4. Extract frontend assets
5. Comprehensive testing
6. Deploy and monitor

## Notes

- Keep old `api/` folder temporarily for backward compatibility
- Test each refactored feature before moving to the next
- Gradual migration, not big-bang deployment
- Document any breaking changes
