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

// ボタンが押されたら処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $now = date('Y-m-d H:i:s');
    $action = $_POST['action'];

    // 当日の打刻レコードがあるかチェック
    $stmt = $pdo->prepare("SELECT * FROM attendances WHERE user_id = ? AND date = ?");
    $stmt->execute([$user_id, $today]);
    $attendance = $stmt->fetch();

    if (!$attendance) {
        // 出勤ボタン以外は新規作成できない
        if ($action !== 'clock_in') {
            $_SESSION['error'] = 'まず出勤ボタンを押してください。';
            header('Location: punch.php');
            exit;
        }

        // 出勤処理（新規レコード作成）
        $stmt = $pdo->prepare("INSERT INTO attendances (user_id, date, clock_in) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $today, $now]);
        $_SESSION['message'] = '出勤を記録しました。';
    } else {
        // 既存レコードに対して打刻
        $column = [
            'break_start' => '休憩開始',
            'break_end' => '休憩終了',
            'clock_out' => '退勤'
        ][$action] ?? null;

        if ($column) {
            $stmt = $pdo->prepare("UPDATE attendances SET {$action} = ? WHERE id = ?");
            $stmt->execute([$now, $attendance['id']]);
            $_SESSION['message'] = $column . 'を記録しました。';
        }
    }

    header('Location: punch.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>打刻画面</title>
</head>
<body>
  <h1>打刻メニュー（<?php echo htmlspecialchars($user['name']); ?>さん）</h1>

  <?php if (!empty($_SESSION['message'])): ?>
    <p style="color: green;"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></p>
  <?php endif; ?>

  <?php if (!empty($_SESSION['error'])): ?>
    <p style="color: red;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
  <?php endif; ?>

  <form method="POST">
    <button type="submit" name="action" value="clock_in">出勤</button>
    <button type="submit" name="action" value="break_start">休憩開始</button>
    <button type="submit" name="action" value="break_end">休憩終了</button>
    <button type="submit" name="action" value="clock_out">退勤</button>
  </form>

  <p><a href="menu.php">← メニューへ戻る</a></p>
</body>
</html>
