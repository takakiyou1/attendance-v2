<?php
session_name('attendance_session');
session_set_cookie_params([
    'path' => '/attendance-v2',
    'domain' => 'localhost',
    'secure' => false,       // ローカルなら false（https で動かすなら true）
    'httponly' => true,
    'samesite' => 'None'     // ← ★ここを 'None' に変更！
]);
session_start();

require __DIR__ . '/../config/database.php';
header('Content-Type: application/json; charset=utf-8');

// ✅ ログイン済みなら誰でもOK
if (empty($_SESSION['user'])) {
    echo json_encode(['error' => 'no_session']);
    exit;
}


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

echo json_encode($events, JSON_UNESCAPED_UNICODE);
