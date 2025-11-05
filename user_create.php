<?php
session_start();
require 'db.php';

// 管理者チェック
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = $_POST['name'];
    $email    = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = $_POST['role']; // 'employee' or 'admin'
    $pay_type = $_POST['pay_type']; // 'hourly' or 'fixed'
    $pay_rate = $_POST['pay_rate'];

    // メール重複チェック
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        $error = 'このメールアドレスは既に登録されています';
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, pay_type, pay_rate) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password, $role, $pay_type, $pay_rate]);
        header('Location: user_list.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>ユーザー新規登録</title>
</head>
<body>
  <h1>➕ ユーザー新規登録</h1>

  <?php if ($error): ?>
    <p style="color: red;"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>

  <form method="post">
    <label>名前：<input type="text" name="name" required></label><br>
    <label>メールアドレス：<input type="email" name="email" required></label><br>
    <label>パスワード：<input type="password" name="password" required></label><br>

    <label>権限：
      <select name="role" required>
        <option value="employee">スタッフ</option>
        <option value="admin">管理者</option>
      </select>
    </label><br>

    <label>報酬種別：
      <select name="pay_type" required>
        <option value="hourly">時給</option>
        <option value="fixed">固定</option>
      </select>
    </label><br>

    <label>報酬金額：<input type="number" name="pay_rate" required></label><br>

    <button type="submit">登録する</button>
  </form>

  <p><a href="menu.php">← メニューに戻る</a></p>
</body>
</html>
