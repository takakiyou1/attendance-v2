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

// 登録処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $shift_start = $_POST['shift_start'] ?? '';
    $shift_end = $_POST['shift_end'] ?? '';
    $default_weeks = $_POST['default_weeks'] ?? 4;
    $days = $_POST['days'] ?? [];

    if (!$name || !$shift_start || !$shift_end || empty($days)) {
        $message = '全ての項目を入力してください。';
    } else {
        $day_str = implode(',', $days);
        $stmt = $pdo->prepare("
            INSERT INTO shift_templates (name, days, shift_start, shift_end, default_weeks)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $day_str, $shift_start, $shift_end, $default_weeks]);
        $message = 'テンプレートを登録しました。';
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>シフトテンプレート登録</title>
</head>
<body>
  <h1>繰り返しシフトテンプレート登録</h1>

  <?php if ($message): ?>
    <p style="color: green;"><?php echo htmlspecialchars($message); ?></p>
  <?php endif; ?>

  <form method="POST">
    <label>テンプレート名：
      <input type="text" name="name" required>
    </label><br><br>

    <label>勤務時間：</label>
    <input type="time" name="shift_start" required> 〜
    <input type="time" name="shift_end" required><br><br>

    <label>デフォルト週数：
      <input type="number" name="default_weeks" value="4" min="1" max="12">
    </label><br><br>

    <label>対象曜日：</label><br>
    <?php foreach ($day_labels as $i => $label): ?>
      <label>
        <input type="checkbox" name="days[]" value="<?php echo $i; ?>"> <?php echo $label; ?>
      </label>
    <?php endforeach; ?>
    <br><br>

    <button type="submit">テンプレートを登録する</button>
  </form>

  <p><a href="shift_bulk_create.php">← 繰り返しシフト登録へ戻る</a></p>
</body>
</html>
