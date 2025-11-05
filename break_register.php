<?php
session_start();
require 'db.php';

// ログインチェック
if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$user_id = $user['id'];
$today = date('Y-m-d');
$now = date('Y-m-d H:i:s');

$message = '';

// ボタン処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'start') {
            // すでに未完の休憩がある場合は無視
            $check = $pdo->prepare("SELECT COUNT(*) FROM breaks WHERE user_id = ? AND date = ? AND break_end IS NULL");
            $check->execute([$user_id, $today]);
            if ($check->fetchColumn() == 0) {
                $stmt = $pdo->prepare("INSERT INTO breaks (user_id, date, break_start) VALUES (?, ?, ?)");
                $stmt->execute([$user_id, $today, $now]);
                $message = '休憩を開始しました。';
            } else {
                $message = '既に休憩中です。';
            }
        }

        if ($_POST['action'] === 'end') {
            // 未完了の休憩を終了させる
            $stmt = $pdo->prepare("SELECT id FROM breaks WHERE user_id = ? AND date = ? AND break_end IS NULL ORDER BY break_start DESC LIMIT 1");
            $stmt->execute([$user_id, $today]);
            $row = $stmt->fetch();
            if ($row) {
                $update = $pdo->prepare("UPDATE breaks SET break_end = ? WHERE id = ?");
                $update->execute([$now, $row['id']]);
                $message = '休憩を終了しました。';
            } else {
                $message = '休憩開始が記録されていません。';
            }
        }
    }
}

// 今日の休憩履歴取得
$breaks = $pdo->prepare("SELECT * FROM breaks WHERE user_id = ? AND date = ? ORDER BY break_start ASC");
$breaks->execute([$user_id, $today]);
$breaks = $breaks->fetchAll();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>休憩登録</title>
</head>
<body>
  <h1>休憩登録画面</h1>
  <p>ようこそ、<?php echo htmlspecialchars($user['name']); ?> さん</p>

  <?php if ($message): ?>
    <p style="color: green;"><?php echo htmlspecialchars($message); ?></p>
  <?php endif; ?>

  <form method="POST">
    <button type="submit" name="action" value="start">休憩開始</button>
    <button type="submit" name="action" value="end">休憩終了</button>
  </form>

  <h2>本日の休憩履歴</h2>
  <ul>
    <?php foreach ($breaks as $b): ?>
      <li>
        <?php echo htmlspecialchars($b['break_start']); ?> 〜 
        <?php echo $b['break_end'] ? htmlspecialchars($b['break_end']) : '（休憩中）'; ?>
      </li>
    <?php endforeach; ?>
  </ul>

  <p><a href="menu.php">← メニューに戻る</a></p>
</body>
</html>
