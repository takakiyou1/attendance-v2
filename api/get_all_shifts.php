<?php
session_start();
require __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

// 管理者チェック
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    echo json_encode([]);
    exit;
}

// シフトを取得（repeat_idも取得する）
$stmt = $pdo->query("
  SELECT 
    s.id, 
    s.user_id, 
    s.date, 
    s.shift_start, 
    s.shift_end, 
    s.repeat_id,          -- ✅ 繰り返し識別子を追加
    u.name 
  FROM shifts s
  JOIN users u ON s.user_id = u.id
  ORDER BY s.date ASC
");

$events = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $s) {
    $start = new DateTime("{$s['date']} {$s['shift_start']}");
    $end   = new DateTime("{$s['date']} {$s['shift_end']}");
    if ($end <= $start) {
        $end->modify('+1 day');
    }

    // ✅ 繰り返しシフトはタイトルに「※繰り返し」を付ける
    $repeatMark = !empty($s['repeat_id']) ? '※繰り返し ' : '';

    $events[] = [
        'id'     => $s['id'],
        'title'  => $repeatMark . "{$s['name']}（" . substr($s['shift_start'], 0, 5) . "〜" . substr($s['shift_end'], 0, 5) . "）",
        'start'  => $start->format('Y-m-d\TH:i:s'),
        'end'    => $end->format('Y-m-d\TH:i:s'),
        'allDay' => false,
    ];
}

echo json_encode($events, JSON_UNESCAPED_UNICODE);
