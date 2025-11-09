<?php
session_start();
require __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

// 管理者チェック（任意：必要な場合のみ残す）
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    echo json_encode(['error' => '権限がありません']);
    exit;
}

// パラメータ取得
$id = $_GET['id'] ?? null;
if (!$id) {
    echo json_encode(['error' => 'ID未指定']);
    exit;
}

// 対象シフトを取得
$stmt = $pdo->prepare("
  SELECT 
    s.id,
    s.user_id,
    s.date,
    s.shift_start,
    s.shift_end,
    s.repeat_id,     -- ✅ ここで繰り返しIDを取得
    u.name
  FROM shifts s
  JOIN users u ON s.user_id = u.id
  WHERE s.id = ?
");
$stmt->execute([$id]);
$shift = $stmt->fetch(PDO::FETCH_ASSOC);

// データがない場合
if (!$shift) {
    echo json_encode(['error' => 'シフトが見つかりません']);
    exit;
}

// JSONで返す
echo json_encode($shift, JSON_UNESCAPED_UNICODE);
