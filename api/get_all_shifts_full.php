<?php
session_start();
require '../db.php';

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->query("
  SELECT s.id, s.user_id, s.date, s.shift_start, s.shift_end, u.name 
  FROM shifts s
  JOIN users u ON s.user_id = u.id
");

$shifts = $stmt->fetchAll();
$events = [];

foreach ($shifts as $s) {
    $startDateTime = new DateTime("{$s['date']} {$s['shift_start']}");
    $endDateTime = new DateTime("{$s['date']} {$s['shift_end']}");

    if ($endDateTime <= $startDateTime) {
        $endDateTime->modify('+1 day');
    }

    $events[] = [
        'id'      => $s['id'],
        'title'   => $s['name'] . "（" . $startDateTime->format('H:i') . "〜" . $endDateTime->format('H:i') . "）",
        'start'   => $startDateTime->format('Y-m-d\TH:i:s'),
        'end'     => $endDateTime->format('Y-m-d\TH:i:s'),
        'allDay'  => false,
        'display' => 'auto'
    ];
}

echo json_encode($events, JSON_UNESCAPED_UNICODE);
