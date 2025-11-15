<?php
session_name('attendance_session');
session_set_cookie_params([
    'path' => '/attendance-v2',
    'domain' => 'localhost',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'None'
]);
session_start();

require __DIR__ . '/../config/database.php';
header('Content-Type: application/json; charset=utf-8');

// ✅ formatHour関数を追加
function formatHour($time) {
    [$h, $m] = explode(':', $time);
    $h = (int)$h;
    if ($h < 9) $h += 24;
    return sprintf('%02d:%02d', $h, $m);
}

if (empty($_SESSION['user'])) {
  echo json_encode([]);
  exit;
}

$user_id = $_SESSION['user']['id'];

$stmt = $pdo->prepare("
  SELECT date, shift_start, shift_end
  FROM shifts
  WHERE user_id = ?
");
$stmt->execute([$user_id]);
$shifts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$events = [];

foreach ($shifts as $s) {
    $start = new DateTime("{$s['date']} {$s['shift_start']}");
    $end   = new DateTime("{$s['date']} {$s['shift_end']}");
    if ($end <= $start) $end->modify('+1 day');

    $startLabel = formatHour($s['shift_start']);
    $endLabel   = formatHour($s['shift_end']);

    $events[] = [
        'title' => "{$startLabel}〜{$endLabel}",
        'start' => $start->format('Y-m-d\TH:i:s'),
        'end'   => $end->format('Y-m-d\TH:i:s'),
        'allDay' => false
    ];
}

echo json_encode($events, JSON_UNESCAPED_UNICODE);
