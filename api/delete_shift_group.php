<?php
require __DIR__ . '/../config/database.php';
header('Content-Type: application/json; charset=utf-8');

$repeat_id = $_POST['repeat_id'] ?? '';

if (!$repeat_id) {
  echo json_encode(['status'=>'error','message'=>'グループIDが指定されていません']);
  exit;
}

$stmt = $pdo->prepare("DELETE FROM shifts WHERE repeat_id = ?");
$stmt->execute([$repeat_id]);

echo json_encode(['status'=>'success','message'=>'繰り返しシフトを全件削除しました']);
