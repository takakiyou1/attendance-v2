<?php
session_start();
require 'db.php';

// 管理者チェック
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// 打刻データの取得
$id = $_GET['id'] ?? null;
if (!$id) {
    echo "無効なIDです。";
    exit;
}

$stmt = $pdo->prepare("SELECT a.*, u.name FROM attendances a JOIN users u ON a.user_id = u.id WHERE a.id = ?");
$stmt->execute([$id]);
$record = $stmt->fetch();

if (!$record) {
    echo "該当データが見つかりません。";
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>打刻編集画面</title>
</head>
<body>
  <h1><?php echo htmlspecialchars($record['name']); ?> さんの打刻修正</h1>

  <form method="POST" action="update_attendance.php">
    <input type="hidden" name="id" value="<?php echo $record['id']; ?>">

    <label>出勤：
      <input type="datetime-local" name="clock_in"
             value="<?php echo $record['clock_in'] ? str_replace(' ', 'T', $record['clock_in']) : ''; ?>">
    </label><br><br>

    <label>休憩開始：
      <input type="datetime-local" name="break_start"
             value="<?php echo $record['break_start'] ? str_replace(' ', 'T', $record['break_start']) : ''; ?>">
    </label><br><br>

    <label>休憩終了：
      <input type="datetime-local" name="break_end"
             value="<?php echo $record['break_end'] ? str_replace(' ', 'T', $record['break_end']) : ''; ?>">
    </label><br><br>

    <label>退勤：
      <input type="datetime-local" name="clock_out"
             value="<?php echo $record['clock_out'] ? str_replace(' ', 'T', $record['clock_out']) : ''; ?>">
    </label><br><br>

    <button type="submit">更新する</button>
  </form>

  <p><a href="admin_history.php">← 戻る</a></p>
</body>
</html>
