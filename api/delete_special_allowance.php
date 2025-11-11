<?php
session_start();
require __DIR__ . '/../config/database.php';
header('Content-Type: application/json; charset=utf-8');

$id = $_POST['id'] ?? null;
if (!$id) {
  echo json_encode(['status'=>'error','message'=>'IDが指定されていません']);
  exit;
}

try {
  $stmt = $pdo->prepare("DELETE FROM special_allowances WHERE id = ?");
  $stmt->execute([$id]);
  echo json_encode(['status'=>'success','message'=>'削除しました']);
} catch (Exception $e) {
  echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
