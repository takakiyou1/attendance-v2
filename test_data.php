<?php
// ✅ Start session FIRST, before any output
session_name('attendance_session');
session_set_cookie_params([
    'path' => '/attendance-v2',
    'domain' => 'localhost',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'None'
]);
session_start();

require __DIR__ . '/config/database.php';

echo "<h2>Current Session:</h2>";
echo "<pre>";
print_r($_SESSION['user'] ?? 'Not logged in');
echo "</pre>";

echo "<h2>Users Table:</h2>";
$users = $pdo->query("SELECT id, name, role FROM users")->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($users);
echo "</pre>";

echo "<h2>Shifts Table:</h2>";
$shifts = $pdo->query("SELECT * FROM shifts LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($shifts);
echo "</pre>";

echo "<h2>Shifts Count by User:</h2>";
$counts = $pdo->query("
    SELECT u.id, u.name, u.role, COUNT(s.id) as shift_count
    FROM users u
    LEFT JOIN shifts s ON u.id = s.user_id
    GROUP BY u.id
")->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($counts);
echo "</pre>";

echo "<h2>Orphaned Shifts (user_id doesn't exist in users table):</h2>";
$orphaned = $pdo->query("
    SELECT s.*, 'USER_NOT_FOUND' as issue
    FROM shifts s
    LEFT JOIN users u ON s.user_id = u.id
    WHERE u.id IS NULL
")->fetchAll(PDO::FETCH_ASSOC);
if (count($orphaned) > 0) {
    echo "<pre>";
    print_r($orphaned);
    echo "</pre>";
} else {
    echo "<p style='color: green;'>✅ No orphaned shifts found</p>";
}

echo "<h2>All Shifts with User Info:</h2>";
$all_shifts = $pdo->query("
    SELECT s.id, s.user_id, u.name as user_name, s.date, s.shift_start, s.shift_end, s.color
    FROM shifts s
    LEFT JOIN users u ON s.user_id = u.id
    ORDER BY s.date DESC
    LIMIT 20
")->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($all_shifts);
echo "</pre>";

echo "<h2>Test get_all_shifts.php API Logic:</h2>";

if (!empty($_SESSION['user'])) {
    echo "<h3>API Response (get_all_shifts.php logic):</h3>";
    $stmt = $pdo->query("
      SELECT
        s.id,
        s.user_id,
        s.date,
        s.shift_start,
        s.shift_end,
        s.repeat_id,
        s.color,
        u.name
      FROM shifts s
      JOIN users u ON s.user_id = u.id
      ORDER BY s.date ASC
    ");

    $events = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $s) {
        $start = new DateTime("{$s['date']} {$s['shift_start']}");
        $end   = new DateTime("{$s['date']} {$s['shift_end']}");
        if ($end <= $start) $end->modify('+1 day');

        $repeatMark = !empty($s['repeat_id']) ? '※繰り返し ' : '';

        $events[] = [
            'id'     => $s['id'],
            'title'  => $repeatMark . "{$s['name']}（{$s['shift_start']}〜{$s['shift_end']}）",
            'start'  => $start->format('Y-m-d\TH:i:s'),
            'end'    => $end->format('Y-m-d\TH:i:s'),
            'allDay' => false,
            'color'  => $s['color'] ?? '#000000'
        ];
    }

    echo "<pre>";
    echo json_encode($events, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    echo "</pre>";
}
?>
