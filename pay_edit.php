<?php
session_start();
require 'db.php';

if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$user_id = $_GET['user_id'] ?? null;
$month = $_GET['month'] ?? null;

if (!$user_id || !$month) {
    exit('不正なアクセスです。');
}

// ユーザー取得
$stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
if (!$user) {
    exit('ユーザーが見つかりません。');
}

// 既存の手動報酬データ取得
$stmt = $pdo->prepare("SELECT override_amount FROM manual_payments WHERE user_id = ? AND month = ?");
$stmt->execute([$user_id, $month]);
$manual = $stmt->fetch();
$current_amount = $manual ? $manual['override_amount'] : '';

// 保存処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = (int) $_POST['override_amount'];

    // 上書き or 新規登録
    $stmt = $pdo->prepare("
        INSERT INTO manual_payments (user_id, month, override_amount)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE override_amount = VALUES(override_amount)
    ");
    $stmt->execute([$user_id, $month, $amount]);

    header("Location: shift_summary.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>報酬修正</title>
</head>
<body>
  <h1>✏️ 報酬修正：<?= htmlspecialchars($user['name']) ?> / <?= $month ?> 月</h1>

  <form method="post">
    <label>手動報酬金額（円）：<input type="number" name="override_amount" value="<?= htmlspecialchars($current_amount) ?>" required></label><br><br>
    <button type="submit">保存する</button>
  </form>

  <p><a href="shift_summary.php">← 報酬一覧に戻る</a></p>
</body>
</html>
