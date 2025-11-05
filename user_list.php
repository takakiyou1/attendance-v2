<?php
session_start();
require 'db.php';

// 管理者チェック
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC");
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>ユーザー一覧</title>
</head>
<body>
  <h1>👥 ユーザー一覧</h1>
  <table border="1" cellpadding="8">
    <tr>
      <th>ID</th><th>名前</th><th>メール</th><th>権限</th><th>報酬種別</th><th>報酬</th><th>操作</th>
    </tr>
    <?php foreach ($users as $u): ?>
      <tr>
        <td><?= $u['id'] ?></td>
        <td><?= htmlspecialchars($u['name']) ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td><?= $u['role'] ?></td>
        <td><?= $u['pay_type'] ?></td>
        <td><?= $u['pay_rate'] ?></td>
        <td>
          <a href="user_edit.php?id=<?= $u['id'] ?>">編集</a> |
          <a href="user_delete.php?id=<?= $u['id'] ?>" onclick="return confirm('本当に削除しますか？');">削除</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
  <p><a href="user_create.php">＋ ユーザーを追加</a></p>
  <p><a href="menu.php">← メニューに戻る</a></p>
</body>
</html>
