<?php
session_start();
require __DIR__ . '/../config/database.php';
header('Content-Type: application/json; charset=utf-8');

$user_id   = $_POST['user_id'] ?? null;
$month     = $_POST['month'] ?? null;
$new_amount = $_POST['new_amount'] ?? null;

if (!$user_id || !$month || !$new_amount) {
  echo json_encode(['status'=>'error','message'=>'データが不足しています']);
  exit;
}

try {
  // 既に手動上書きがある場合は更新、なければ新規登録
  $stmt = $pdo->prepare("
    INSERT INTO manual_payments (user_id, month, override_amount, created_at)
    VALUES (?, ?, ?, NOW())
    ON DUPLICATE KEY UPDATE override_amount = VALUES(override_amount), created_at = NOW()
  ");
  $stmt->execute([$user_id, $month, $new_amount]);

  echo json_encode(['status'=>'success','message'=>'月別報酬を上書き保存しました']);
} catch (Exception $e) {
  echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
