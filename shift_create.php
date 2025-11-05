<?php
session_start();
require 'db.php';

// ✅ 管理者チェック
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$message = '';

// ✅ フォーム送信処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $date = $_POST['date'];
    $shift_start = $_POST['shift_start'];
    $shift_end = $_POST['shift_end'];

    // バリデーション
    if (!$user_id || !$date || !$shift_start || !$shift_end) {
        $message = 'すべての項目を入力してください。';
    } else {
        // 該当ユーザーの時給を取得
        $stmt = $pdo->prepare("SELECT pay_type, pay_rate FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if (!$user) {
            $message = 'ユーザーが見つかりません。';
        } elseif ($user['pay_type'] === 'fixed') {
            $message = '固定報酬のユーザーにはシフト登録は不要です。';
        } else {
            // シフト登録
            $stmt = $pdo->prepare("
                INSERT INTO shifts (user_id, date, shift_start, shift_end, hourly_wage)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$user_id, $date, $shift_start, $shift_end, $user['pay_rate']]);
            $message = 'シフトを登録しました。';
        }
    }
}

// ✅ ユーザー一覧取得（時給制のみ）
$users = $pdo->query("SELECT id, name FROM users WHERE pay_type = 'hourly' ORDER BY name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>シフト登録（管理者）</title>
</head>
<body>
  <h1>シフト登録フォーム</h1>

  <?php if ($message): ?>
    <p style="color: green;"><?php echo htmlspecialchars($message); ?></p>
  <?php endif; ?>

  <form method="POST">
    <label>ユーザー：
      <select name="user_id" required>
        <option value="">選択してください</option>
        <?php foreach ($users as $u): ?>
          <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['name']); ?></option>
        <?php endforeach; ?>
      </select>
    </label><br><br>

    <label>日付：
      <input type="date" name="date" required>
    </label><br><br>

    <label>開始時刻：
      <input type="time" name="shift_start" required>
    </label><br><br>

    <label>終了時刻：
      <input type="time" name="shift_end" required>
    </label><br><br>

    <button type="submit">登録する</button>
  </form>

  <p><a href="menu.php">← メニューに戻る</a></p>
</body>
</html>
