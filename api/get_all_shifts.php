<?php
session_start();
require '../db.php'; // api配下に配置している前提

header('Content-Type: application/json; charset=utf-8');

// 管理者チェック
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
    $date = $s['date'];
    $startTime = $s['shift_start'];
    $endTime = $s['shift_end'];

    $startDateTime = new DateTime("$date $startTime");
    $endDateTime   = new DateTime("$date $endTime");

    // 翌日5:00まで対応：終了が開始より前（深夜またぎ）は翌日扱いに
    if ($endDateTime <= $startDateTime) {
        $endDateTime->modify('+1 day');
    }

    // タイトルに時間帯を表示（例：田中太郎（20:00〜翌5:00））
    $startLabel = $startDateTime->format('H:i');
    $endLabel = $endDateTime->format('H:i');
    $title = "{$s['name']}（{$startLabel}〜{$endLabel}）";

    $events[] = [
        'id'      => $s['id'],
        'title'   => $title,
        'start'   => $startDateTime->format('Y-m-d\TH:i:s'),
        'end'     => $endDateTime->format('Y-m-d\TH:i:s'),
        'allDay'  => false,
        'display' => 'auto' // ✅ 月表示でバーを伸ばさず、時間ありのまま表示
    ];
}

echo json_encode($events, JSON_UNESCAPED_UNICODE);
