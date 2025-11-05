<?php
session_start();
require 'db.php';

// 管理者チェック
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$message = '';
$day_labels = ['日','月','火','水','木','金','土'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $label = $_POST['label'] ?? '';
    $time_start = $_POST['time_start'] ?? '';
    $time_end = $_POST['time_end'] ?? '';
    $required_count = $_POST['required_count'] ?? '';
    $days = $_POST['days'] ?? [];
    $note = $_POST['note'] ?? '';
    $enabled = !empty($_POST['enabled']) ? 1 : 0;

    if (!$label || !$time_start || !$time_end || !$required_count || empty($days)) {
        $message = '全ての必須項目を入力してください。';
    } else {
        $day_str = implode(',', $days);
        $stmt = $pdo->prepare("
            INSERT INTO shift_alert_rules
            (label, time_start, time_end, required_count, days_of_week, note, enabled)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$label, $time_start, $time_end, $required_count, $day_str, $note, $enabled]);
        $message = 'アラート条件を登録しました。';
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>アラート条件の登録</title>
</head>
<body>
  <h1>シフト条件アラートの登録（管理者）</h1>

  <?php if ($message): ?>
    <p style="color: green;"><?php echo htmlspecialchars($message); ?></p>
  <?php endif; ?>

  <form method="POST">
    <label>条件名：
      <input type="text" name="label" required>
    </label><br><br>

    <label>時間帯：
      <input type="time" name="time_start" required> 〜
      <input type="time" name="time_end" required>
    </label><br><br>

    <label>必要人数：
      <input type="number" name="required_count" min="1" required>
    </label><br><br>

    <label>該当曜日：</label><br>
    <?php foreach ($day_labels as $i => $label): ?>
      <label>
        <input type="checkbox" name="days[]" value="<?php echo $i; ?>"> <?php echo $label; ?>
      </label>
    <?php endforeach; ?>
    <br><br>

    <label>メモ：
      <textarea name="note" rows="2" cols="40"></textarea>
    </label><br><br>

    <label>
      <input type="checkbox" name="enabled" value="1" checked> この条件を有効にする
    </label><br><br>

    <button type="submit">登録する</button>
  </form>

  <p><a href="shift_list.php">← シフト一覧に戻る</a></p>
</body>
</html>
