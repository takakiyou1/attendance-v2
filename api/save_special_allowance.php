<?php
session_start();
require __DIR__ . '/../config/database.php';
header('Content-Type: application/json; charset=utf-8');

$user_id = $_POST['user_id'] ?? null;
$month   = $_POST['month'] ?? null;
$title   = $_POST['title'] ?? null;
$amount  = $_POST['amount'] ?? 0;

if (!$user_id || !$month || !$title) {
  echo json_encode(['status'=>'error','message'=>'入力が不足しています']);
  exit;
}

try {
  $stmt = $pdo->prepare("
    INSERT INTO special_allowances (user_id, month, title, amount)
    VALUES (?, ?, ?, ?)
  ");
  $stmt->execute([$user_id, $month, $title, $amount]);

  echo json_encode(['status'=>'success','message'=>'手当を追加しました']);
} catch (Exception $e) {
  echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
