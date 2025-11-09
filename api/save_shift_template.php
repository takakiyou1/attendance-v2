<?php
require __DIR__ . '/../config/database.php';
header('Content-Type: application/json; charset=utf-8');

$name = $_POST['name'] ?? '';
$user_id = $_POST['user_id'] ?? '';
$shift_start = $_POST['shift_start'] ?? '';
$shift_end = $_POST['shift_end'] ?? '';

// 必須チェック
if (!$name || !$user_id || !$shift_start || !$shift_end) {
  echo json_encode(['status'=>'error','message'=>'データが不足しています']);
  exit;
}

try {
  // 既存テーブルに対応（days と default_weeks はデフォルト値を代入）
  $stmt = $pdo->prepare("
    INSERT INTO shift_templates (name, days, shift_start, shift_end, default_weeks, created_at)
    VALUES (?, '', ?, ?, 1, NOW())
  ");
  $stmt->execute([$name, $shift_start, $shift_end]);

  echo json_encode(['status'=>'success','message'=>'テンプレートを保存しました']);
} catch (Exception $e) {
  echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
