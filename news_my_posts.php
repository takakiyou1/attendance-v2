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

// 自分が投稿したニュースを取得
$stmt = $pdo->prepare("
  SELECT * FROM news 
  WHERE user_id = ? 
  ORDER BY display_date DESC, created_at DESC
");
$stmt->execute([$user_id]);
$news_list = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>自分の投稿履歴</title>
</head>
<body>
  <h1><?php echo htmlspecialchars($user_name); ?>さんの投稿履歴</h1>

  <?php if (empty($news_list)): ?>
    <p>まだ投稿がありません。</p>
  <?php else: ?>
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
  <?php endif; ?>

  <p><a href="menu.php">← メニューに戻る</a></p>
</body>
</html>
