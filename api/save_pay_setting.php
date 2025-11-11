<?php
session_start();
require __DIR__ . '/../config/database.php';
header('Content-Type: application/json; charset=utf-8');

$user_id    = $_POST['user_id'] ?? null;
$pay_type   = $_POST['pay_type'] ?? null;
$pay_rate   = $_POST['pay_rate'] ?? null;
$start_date = $_POST['start_date'] ?? null;

if (!$user_id || !$pay_type || !$pay_rate || !$start_date) {
  echo json_encode(['status'=>'error','message'=>'入力が不足しています']);
  exit;
}

try {
  // usersテーブルは「最新状態」を反映（管理画面の表示用）
  $stmt = $pdo->prepare("UPDATE users SET pay_type=?, pay_rate=? WHERE id=?");
  $stmt->execute([$pay_type, $pay_rate, $user_id]);

  // 現在有効な履歴があり、今回のstart_dateより前ならend_dateを更新
  $stmt = $pdo->prepare("
    UPDATE pay_rate_history 
    SET end_date = DATE_SUB(?, INTERVAL 1 DAY)
    WHERE user_id = ? AND end_date IS NULL
  ");
  $stmt->execute([$start_date, $user_id]);

  // 新しい履歴を追加（選択された開始日で登録）
  $stmt = $pdo->prepare("
    INSERT INTO pay_rate_history (user_id, pay_type, pay_rate, start_date)
    VALUES (?, ?, ?, ?)
  ");
  $stmt->execute([$user_id, $pay_type, $pay_rate, $start_date]);

  echo json_encode(['status'=>'success','message'=>'報酬設定を更新しました（適用開始日を登録）']);
} catch (Exception $e) {
  echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
