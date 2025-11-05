<?php
session_start();
require 'db.php';

if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "不正なアクセスです。";
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
$stmt->execute([$id]);
$news = $stmt->fetch();

if (!$news) {
    echo "該当ニュースが見つかりません。";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $display_date = $_POST['display_date'] ?? '';
    $is_pinned = !empty($_POST['is_pinned']) ? 1 : 0;

    if ($title && $content && $display_date) {
        $update = $pdo->prepare("
            UPDATE news SET title = ?, content = ?, display_date = ?, is_pinned = ? WHERE id = ?
        ");
        $update->execute([$title, $content, $display_date, $is_pinned, $id]);
        header("Location: news_manage.php");
        exit;
    } else {
        $message = "すべての項目を入力してください。";
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>ニュース編集</title>
</head>
<body>
  <h1>ニュース編集</h1>

  <?php if (!empty($message)): ?>
    <p style="color: red;"><?php echo htmlspecialchars($message); ?></p>
  <?php endif; ?>

  <form method="POST">
    <label>タイトル：<br>
      <input type="text" name="title" value="<?php echo htmlspecialchars($news['title']); ?>" required>
    </label><br><br>

    <label>本文：<br>
      <textarea name="content" rows="5" cols="60" required><?php echo htmlspecialchars($news['content']); ?></textarea>
    </label><br><br>

    <label>表示日：<br>
      <input type="date" name="display_date" value="<?php echo htmlspecialchars($news['display_date']); ?>" required>
    </label><br><br>

    <label>
      <input type="checkbox" name="is_pinned" value="1" <?php if ($news['is_pinned']) echo 'checked'; ?>> 🔔 重要（ピン留め）
    </label><br><br>

    <button type="submit">更新する</button>
  </form>

  <p><a href="news_manage.php">← 一覧に戻る</a></p>
</body>
</html>
