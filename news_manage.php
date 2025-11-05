<?php
session_start();
require 'db.php';

// ログインチェック
if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// ニュース全件取得（降順）
$stmt = $pdo->query("SELECT * FROM news ORDER BY display_date DESC, created_at DESC");
$news_list = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>掲示板一覧・編集・削除</title>
</head>
<body>
  <h1>社内掲示板 管理画面</h1>

  <p><a href="news_create.php">＋ 新規投稿</a></p>

  <table border="1" cellpadding="6" cellspacing="0">
    <tr>
      <th>表示日</th>
      <th>タイトル</th>
      <th>操作</th>
    </tr>
    <?php foreach ($news_list as $n): ?>
      <tr>
        <td><?php echo htmlspecialchars($n['display_date']); ?></td>
        <td><?php echo htmlspecialchars($n['title']); ?></td>
        <td>
          <a href="news_edit.php?id=<?php echo $n['id']; ?>">編集</a> |
          <a href="news_delete.php?id=<?php echo $n['id']; ?>" onclick="return confirm('本当に削除しますか？');">削除</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>

  <p><a href="menu.php">← メニューに戻る</a></p>
</body>
</html>
