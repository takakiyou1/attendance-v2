<?php
session_start();
require 'db.php';

// 管理者チェック
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    exit('IDが指定されていません');
}

// ユーザー取得
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    exit('ユーザーが見つかりません');
}

// 更新処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = $_POST['name'];
    $email    = $_POST['email'];
    $role     = $_POST['role'];     // 'employee' or 'admin'
    $pay_type = $_POST['pay_type']; // 'hourly' or 'fixed'
    $pay_rate = $_POST['pay_rate'];

    $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, role=?, pay_type=?, pay_rate=? WHERE id=?");
    $stmt->execute([$name, $email, $role, $pay_type, $pay_rate, $id]);

    header('Location: user_list.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>ユーザー編集</title>
</head>
<body>
  <h1>✏️ ユーザー編集</h1>

  <form method="post">
    <label>名前：<input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required></label><br>
    <label>メールアドレス：<input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required></label><br>

    <label>権限：
      <select name="role" required>
        <option value="employee" <?= $user['role'] === 'employee' ? 'selected' : '' ?>>スタッフ</option>
        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>管理者</option>
      </select>
    </label><br>

    <label>報酬種別：
      <select name="pay_type" required>
        <option value="hourly" <?= $user['pay_type'] === 'hourly' ? 'selected' : '' ?>>時給</option>
        <option value="fixed" <?= $user['pay_type'] === 'fixed' ? 'selected' : '' ?>>固定</option>
      </select>
    </label><br>

    <label>報酬金額：<input type="number" name="pay_rate" value="<?= $user['pay_rate'] ?>" required></label><br>

    <button type="submit">更新する</button>
  </form>

  <p><a href="user_list.php">← 一覧に戻る</a></p>
</body>
</html>
