<?php
session_start();
require '../db.php'; // ← パスに注意（api/配下）

header('Content-Type: application/json; charset=utf-8');

// ログインチェック
if (empty($_SESSION['user'])) {
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['user']['id'];

// ユーザーのシフトを取得
$stmt = $pdo->prepare("
  SELECT date, shift_start, shift_end 
  FROM shifts 
  WHERE user_id = ?
");
$stmt->execute([$user_id]);
$shifts = $stmt->fetchAll();

// FullCalendar用に整形
$events = [];

foreach ($shifts as $s) {
    $start = $s['date'] . 'T' . $s['shift_start'];
    $end   = $s['date'] . 'T' . $s['shift_end'];
    $events[] = [
        'title' => 'シフト',
        'start' => $start,
        'end'   => $end,
        'allDay' => false
    ];
}

echo json_encode($events, JSON_UNESCAPED_UNICODE);
