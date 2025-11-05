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

$news_id = $_GET['id'] ?? null;
if (!$news_id) {
    echo "不正なアクセスです。";
    exit;
}

// 投稿内容の取得
$stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
$stmt->execute([$news_id]);
$news = $stmt->fetch();

if (!$news) {
    echo "該当ニュースが見つかりません。";
    exit;
}

// コメント投稿処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['comment'])) {
    $comment = trim($_POST['comment']);
    if ($comment !== '') {
        $insert = $pdo->prepare("INSERT INTO comments (news_id, user_id, content) VALUES (?, ?, ?)");
        $insert->execute([$news_id, $user_id, $comment]);
        header("Location: news_view.php?id=" . $news_id); // リロードして二重送信防止
        exit;
    }
}

// コメント一覧の取得（投稿日時順）
$comments_stmt = $pdo->prepare("
  SELECT c.*, u.name FROM comments c
  JOIN users u ON c.user_id = u.id
  WHERE c.news_id = ?
  ORDER BY c.created_at ASC
");
$comments_stmt->execute([$news_id]);
$comments = $comments_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>ニュース詳細とコメント</title>
</head>
<body>
  <h1><?php echo htmlspecialchars($news['title']); ?></h1>
  <p><strong>【<?php echo htmlspecialchars($news['display_date']); ?>】</strong></p>
  <p><?php echo nl2br(htmlspecialchars($news['content'])); ?></p>

  <hr>

  <h2>📝 コメント</h2>

  <?php if (empty($comments)): ?>
    <p>まだコメントはありません。</p>
  <?php else: ?>
    <ul>
      <?php foreach ($comments as $c): ?>
        <li>
          <?php echo htmlspecialchars($c['name']); ?>：
          <?php echo nl2br(htmlspecialchars($c['content'])); ?>  
          <small>（<?php echo $c['created_at']; ?>）</small>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <form method="POST">
    <label>コメントを入力：</label><br>
    <textarea name="comment" rows="3" cols="60" required></textarea><br>
    <button type="submit">送信</button>
  </form>

  <p><a href="news_board.php">← 掲示板一覧に戻る</a></p>
</body>
</html>
