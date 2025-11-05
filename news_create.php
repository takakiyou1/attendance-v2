<?php
session_start();
require 'db.php';

// ログインチェック（全ユーザーOK）
if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $display_date = $_POST['display_date'] ?? '';
    $is_pinned = !empty($_POST['is_pinned']) ? 1 : 0;
    $user_id = $_SESSION['user']['id']; // 投稿者ID

    if (!$title || !$content || !$display_date) {
        $message = '全ての項目を入力してください。';
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO news (title, content, display_date, is_pinned, user_id)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$title, $content, $display_date, $is_pinned, $user_id]);
        $message = '掲示板に投稿しました。';
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>掲示板投稿</title>
</head>
<body>
  <h1>社内掲示板 投稿フォーム</h1>

  <?php if ($message): ?>
    <p style="color: green;"><?php echo htmlspecialchars($message); ?></p>
  <?php endif; ?>

  <form method="POST">
    <label>タイトル：<br>
      <input type="text" name="title" style="width: 300px;" required>
    </label><br><br>

    <label>本文：<br>
      <textarea name="content" rows="5" cols="60" required></textarea>
    </label><br><br>

    <label>表示日（いつ掲示するか）：<br>
      <input type="date" name="display_date" required>
    </label><br><br>

    <label>
      <input type="checkbox" name="is_pinned" value="1"> 🔔 重要（ピン留め）
    </label><br><br>

    <button type="submit">投稿する</button>
  </form>

  <p><a href="menu.php">← メニューに戻る</a></p>
</body>
</html>
