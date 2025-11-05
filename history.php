<?php
session_start();
require 'db.php';

// ログインしてなければ login.php へリダイレクト
if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$user_id = $user['id'];

// 勤怠履歴取得
$stmt = $pdo->prepare("SELECT * FROM attendances WHERE user_id = ? ORDER BY date DESC");
$stmt->execute([$user_id]);
$records = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>勤怠履歴</title>
  <style>
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
  </style>
</head>
<body>
  <h1><?php echo htmlspecialchars($user['name']); ?>さんの勤怠履歴</h1>

  <table>
    <thead>
      <tr>
        <th>日付</th>
        <th>出勤</th>
        <th>休憩開始</th>
        <th>休憩終了</th>
        <th>退勤</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($records as $row): ?>
        <tr>
          <td><?php echo htmlspecialchars($row['date']); ?></td>
          <td><?php echo htmlspecialchars($row['clock_in'] ?? ''); ?></td>
          <td><?php echo htmlspecialchars($row['break_start'] ?? ''); ?></td>
          <td><?php echo htmlspecialchars($row['break_end'] ?? ''); ?></td>
          <td><?php echo htmlspecialchars($row['clock_out'] ?? ''); ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <p><a href="menu.php">← メニューに戻る</a></p>
</body>
</html>
