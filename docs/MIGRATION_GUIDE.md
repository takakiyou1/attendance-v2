# Migration Guide - MVC Refactoring

## Overview
This guide explains how to complete the MVC refactoring of the attendance system. Follow these steps in order.

## Current Status

### ✅ Completed
1. Created core infrastructure:
   - `core/Database.php` - Database singleton
   - `core/Session.php` - Session manager
   - `core/Response.php` - HTTP response helper
2. Created configuration files:
   - `config/app.php` - Application settings
   - `config/session.php` - Session configuration
   - `config/database_new.php` - New DB config format
3. Created documentation:
   - `docs/ARCHITECTURE.md` - Architecture overview
   - `REFACTORING_PLAN.md` - Original plan

### 🔄 In Progress
- Model layer implementation
- Controller refactoring

### ⏳ Pending
- View layouts
- Asset extraction (CSS/JS)
- Legacy file cleanup

## Step-by-Step Migration

### Step 1: Update BaseModel

Replace `core/Model.php` with this improved version:

```php
<?php
require_once __DIR__ . '/Database.php';

class Model {
    protected string $table;
    protected string $primaryKey = 'id';
    protected PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    /**
     * Find record by ID
     */
    public function find($id): ?array {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return Database::fetchOne($sql, [$id]);
    }

    /**
     * Get all records
     */
    public function all(): array {
        $sql = "SELECT * FROM {$this->table}";
        return Database::fetchAll($sql);
    }

    /**
     * Create new record
     */
    public function create(array $data): int {
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $fields),
            implode(', ', $placeholders)
        );

        Database::query($sql, array_values($data));
        return (int)Database::lastInsertId();
    }

    /**
     * Update record
     */
    public function update($id, array $data): bool {
        $fields = array_keys($data);
        $setClause = implode(' = ?, ', $fields) . ' = ?';

        $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = ?";

        $params = array_values($data);
        $params[] = $id;

        Database::query($sql, $params);
        return true;
    }

    /**
     * Delete record
     */
    public function delete($id): bool {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        Database::query($sql, [$id]);
        return true;
    }

    /**
     * Find records by column value
     */
    public function where(string $column, $value): array {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = ?";
        return Database::fetchAll($sql, [$value]);
    }
}
```

### Step 2: Create Models Directory

Create `app/Models/` if it doesn't exist:
```bash
mkdir -p app/Models
```

### Step 3: Create User Model

Create `app/Models/User.php`:

```php
<?php
require_once __DIR__ . '/../../core/Model.php';

class User extends Model {
    protected string $table = 'users';

    /**
     * Get all employees (non-admin users)
     */
    public function getEmployees(): array {
        return $this->where('role', 'employee');
    }

    /**
     * Find user by credentials
     */
    public function findByCredentials(string $username, string $password): ?array {
        $sql = "SELECT * FROM {$this->table} WHERE name = ?";
        $user = Database::fetchOne($sql, [$username]);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return null;
    }

    /**
     * Check if username exists
     */
    public function usernameExists(string $username, ?int $excludeId = null): bool {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE name = ?";
        $params = [$username];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = Database::fetchOne($sql, $params);
        return $result['count'] > 0;
    }
}
```

### Step 4: Create Shift Model

Create `app/Models/Shift.php`:

```php
<?php
require_once __DIR__ . '/../../core/Model.php';

class Shift extends Model {
    protected string $table = 'shifts';

    /**
     * Get all shifts with user information
     */
    public function getAllWithUsers(): array {
        $sql = "
            SELECT
                s.id, s.user_id, s.date, s.shift_start, s.shift_end,
                s.repeat_id, s.color,
                u.name as user_name
            FROM {$this->table} s
            JOIN users u ON s.user_id = u.id
            ORDER BY s.date ASC
        ";
        return Database::fetchAll($sql);
    }

    /**
     * Get shifts by user ID
     */
    public function getByUser(int $userId): array {
        return $this->where('user_id', $userId);
    }

    /**
     * Get shifts by date
     */
    public function getByDate(string $date): array {
        $sql = "
            SELECT s.*, u.name as user_name
            FROM {$this->table} s
            JOIN users u ON s.user_id = u.id
            WHERE s.date = ?
            ORDER BY s.shift_start ASC
        ";
        return Database::fetchAll($sql, [$date]);
    }

    /**
     * Get shifts by date range
     */
    public function getByDateRange(string $startDate, string $endDate): array {
        $sql = "
            SELECT s.*, u.name as user_name
            FROM {$this->table} s
            JOIN users u ON s.user_id = u.id
            WHERE s.date BETWEEN ? AND ?
            ORDER BY s.date ASC, s.shift_start ASC
        ";
        return Database::fetchAll($sql, [$startDate, $endDate]);
    }

    /**
     * Check for overlapping shifts
     */
    public function hasOverlap(int $userId, string $date, string $start, string $end, ?int $excludeId = null): bool {
        $sql = "
            SELECT COUNT(*) as count
            FROM {$this->table}
            WHERE user_id = ?
              AND date = ?
              AND id != ?
              AND (
                (shift_start < ? AND shift_end > ?) OR
                (shift_start < ? AND shift_end > ?) OR
                (shift_start >= ? AND shift_end <= ?)
              )
        ";

        $result = Database::fetchOne($sql, [
            $userId, $date, $excludeId ?: 0,
            $end, $start,
            $start, $end,
            $start, $end
        ]);

        return $result['count'] > 0;
    }

    /**
     * Delete shifts by repeat_id
     */
    public function deleteByRepeatId(string $repeatId): int {
        $sql = "DELETE FROM {$this->table} WHERE repeat_id = ?";
        $stmt = Database::query($sql, [$repeatId]);
        return $stmt->rowCount();
    }

    /**
     * Update shifts by repeat_id
     */
    public function updateByRepeatId(string $repeatId, array $data): int {
        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            $fields[] = "{$key} = ?";
            $params[] = $value;
        }

        $params[] = $repeatId;

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE repeat_id = ?";
        $stmt = Database::query($sql, $params);
        return $stmt->rowCount();
    }
}
```

### Step 5: Update Controllers

Update `app/controllers/ShiftController.php` to use models:

```php
<?php
require_once __DIR__ . '/../Models/Shift.php';
require_once __DIR__ . '/../Models/User.php';

class ShiftController extends Controller
{
    private Shift $shiftModel;
    private User $userModel;

    public function __construct() {
        parent::__construct();
        $this->shiftModel = new Shift();
        $this->userModel = new User();
    }

    // API method for getting all shifts
    public function apiAll() {
        if (!Session::isLoggedIn()) {
            Response::error('no_session', 401);
        }

        $shifts = $this->shiftModel->getAllWithUsers();

        $events = [];
        foreach ($shifts as $s) {
            $start = new DateTime("{$s['date']} {$s['shift_start']}");
            $end = new DateTime("{$s['date']} {$s['shift_end']}");
            if ($end <= $start) $end->modify('+1 day');

            $repeatMark = !empty($s['repeat_id']) ? '※繰り返し ' : '';

            $events[] = [
                'id' => $s['id'],
                'title' => $repeatMark . "{$s['user_name']}（{$s['shift_start']}〜{$s['shift_end']}）",
                'start' => $start->format('Y-m-d\TH:i:s'),
                'end' => $end->format('Y-m-d\TH:i:s'),
                'allDay' => false,
                'color' => $s['color'] ?? '#000000'
            ];
        }

        Response::json($events);
    }

    // Existing methods...
}
```

### Step 6: Update Public Index

Update `public/index.php` to use new infrastructure:

```php
<?php
// Load core classes
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/View.php';

// Load configurations
$appConfig = require __DIR__ . '/../config/app.php';
$dbConfig = require __DIR__ . '/../config/database_new.php';
$sessionConfig = require __DIR__ . '/../config/session.php';

// Initialize services
Database::setConfig($dbConfig);
Session::setConfig($sessionConfig);
Session::start();

// Create router
$router = new Router();

// Load routes (existing route definitions...)
// Keep all existing routes unchanged

// Run router
$router->run();
```

### Step 7: Create API Routes

Add new API routes to `public/index.php`:

```php
// API Routes for AJAX requests
$router->get('/shift/api/all', 'ShiftController@apiAll');
$router->get('/shift/api/my', 'ShiftController@apiMy');
$router->get('/shift/api/by-date', 'ShiftController@apiByDate');
```

### Step 8: Update View Files

Update `app/views/shift/view_all.php` to use new API endpoint:

```javascript
// Change from:
events: '/attendance-v2/api/get_all_shifts.php',

// To:
events: '/attendance-v2/public/index.php/shift/api/all',
```

## Testing Checklist

After each step, test these features:

- [ ] Login/logout works
- [ ] Admin can view shift calendar
- [ ] Admin can create/edit/delete shifts
- [ ] Staff can view all shifts (read-only)
- [ ] Staff can view their own shifts
- [ ] Clicking dates shows shift details
- [ ] Repeat shifts work correctly
- [ ] User management works
- [ ] Pay calculation works

## Rollback Plan

If issues occur:
1. The old `api/` folder still exists
2. Old `config/database.php` still works
3. Controllers can temporarily use both old and new code
4. Views can use either old or new API endpoints

## Benefits After Migration

1. **No more duplicate session code** - All centralized in Session class
2. **Cleaner controllers** - Business logic in models
3. **Reusable code** - BaseModel provides CRUD for all tables
4. **Better error handling** - Centralized in Response class
5. **Easier testing** - Models can be tested independently
6. **Better security** - Prepared statements in all queries

## Next Steps

1. Follow steps 1-8 above
2. Test thoroughly after each step
3. Move old `api/*.php` files to `legacy/api/`
4. Create view layouts (see ARCHITECTURE.md)
5. Extract CSS/JS to separate files
6. Update documentation

## Need Help?

- Check `docs/ARCHITECTURE.md` for architecture overview
- Check existing model/controller code for examples
- Test in development environment first
- Keep backups before major changes
