<?php
session_start();
require 'db.php';

// ログインチェック
if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user']['id'];
$user_name = $_SESSION['user']['name'];

// 自分のシフト一覧を取得（今日以降）
$stmt = $pdo->prepare("
  SELECT * FROM shifts 
  WHERE user_id = ? AND date >= CURDATE()
  ORDER BY date ASC, shift_start ASC
");
$stmt->execute([$user_id]);
$shifts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>自分のシフト確認</title>
</head>
<body>
  <h1><?php echo htmlspecialchars($user_name); ?>さんのシフト一覧</h1>

  <?php if (empty($shifts)): ?>
    <p>現在表示できるシフトはありません。</p>
  <?php else: ?>
    <table border="1" cellpadding="6" cellspacing="0">
      <tr>
        <th>日付</th>
        <th>開始</th>
        <th>終了</th>
        <th>時給</th>
      </tr>
      <?php foreach ($shifts as $s): ?>
        <tr>
          <td><?php echo htmlspecialchars($s['date']); ?></td>
          <td><?php echo htmlspecialchars(substr($s['shift_start'], 0, 5)); ?></td>
          <td><?php echo htmlspecialchars(substr($s['shift_end'], 0, 5)); ?></td>
          <td><?php echo htmlspecialchars($s['hourly_wage']); ?> 円</td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>

  <p><a href="menu.php">← メニューに戻る</a></p>
</body>
</html>
