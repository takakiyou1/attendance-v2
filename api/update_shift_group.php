<?php
require __DIR__ . '/../config/database.php';
header('Content-Type: application/json; charset=utf-8');

$repeat_id   = $_POST['repeat_id'] ?? '';
$user_id     = $_POST['user_id'] ?? '';
$shift_start = $_POST['shift_start'] ?? '';
$shift_end   = $_POST['shift_end'] ?? '';

if (!$repeat_id || !$user_id || !$shift_start || !$shift_end) {
  echo json_encode(['status'=>'error','message'=>'必要な情報が足りません']);
  exit;
}

try {
  $stmt = $pdo->prepare("
    UPDATE shifts 
    SET user_id = ?, shift_start = ?, shift_end = ?
    WHERE repeat_id = ?
  ");
  $stmt->execute([$user_id, $shift_start, $shift_end, $repeat_id]);

  $count = $stmt->rowCount();
  echo json_encode(['status'=>'success','message'=>"{$count}件のシフトを更新しました"]);
} catch (Exception $e) {
  echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
