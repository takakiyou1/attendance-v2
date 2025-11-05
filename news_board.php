<?php
session_start();
require 'db.php';

// ログインチェック
if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// 検索条件
$keyword = $_GET['keyword'] ?? '';
$month = $_GET['month'] ?? '';

// 条件式
$where = "display_date <= CURDATE()";
$params = [];

if ($month) {
    $where .= " AND DATE_FORMAT(display_date, '%Y-%m') = ?";
    $params[] = $month;
}

if ($keyword) {
    $where .= " AND (title LIKE ? OR content LIKE ?)";
    $params[] = "%$keyword%";
    $params[] = "%$keyword%";
}

// ピン留め（is_pinned = 1）
$pinned_stmt = $pdo->prepare("SELECT * FROM news WHERE $where AND is_pinned = 1 ORDER BY display_date DESC, created_at DESC");
$pinned_stmt->execute($params);
$pinned_news = $pinned_stmt->fetchAll();

// 通常投稿（is_pinned = 0）
$normal_stmt = $pdo->prepare("SELECT * FROM news WHERE $where AND is_pinned = 0 ORDER BY display_date DESC, created_at DESC");
$normal_stmt->execute($params);
$normal_news = $normal_stmt->fetchAll();

// 月別選択リスト
$month_stmt = $pdo->query("SELECT DISTINCT DATE_FORMAT(display_date, '%Y-%m') AS ym FROM news WHERE display_date <= CURDATE() ORDER BY ym DESC");
$months = $month_stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>社内掲示板</title>
</head>
<body>
  <h1>📢 社内掲示板</h1>

  <form method="GET" style="margin-bottom: 20px;">
    <label>キーワード検索：
      <input type="text" name="keyword" value="<?php echo htmlspecialchars($keyword); ?>">
    </label>
    <label>月で絞り込み：
      <select name="month">
        <option value="">-- 全期間 --</option>
        <?php foreach ($months as $ym): ?>
          <option value="<?php echo $ym; ?>" <?php if ($ym === $month) echo 'selected'; ?>>
            <?php echo $ym; ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>
    <button type="submit">検索</button>
  </form>

  <!-- 📌 ピン留め表示 -->
  <?php if (!empty($pinned_news)): ?>
    <h2>📌 重要なお知らせ</h2>
    <?php foreach ($pinned_news as $n): ?>
      <div style="border: 2px solid #f00; padding: 10px; margin-bottom: 15px; background: #fff0f0;">
        <strong>【<?php echo htmlspecialchars($n['display_date']); ?>】</strong><br>
        <h3><?php echo htmlspecialchars($n['title']); ?></h3>
        <p><?php echo nl2br(htmlspecialchars($n['content'])); ?></p>
        <p><a href="news_view.php?id=<?php echo $n['id']; ?>">📝 コメントを見る</a></p>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <!-- 通常のお知らせ -->
  <?php if (empty($normal_news)): ?>
    <p>表示できる投稿はありません。</p>
  <?php else: ?>
    <?php foreach ($normal_news as $n): ?>
      <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 15px;">
        <strong>【<?php echo htmlspecialchars($n['display_date']); ?>】</strong><br>
        <h3><?php echo htmlspecialchars($n['title']); ?></h3>
        <p><?php echo nl2br(htmlspecialchars($n['content'])); ?></p>
        <p><a href="news_view.php?id=<?php echo $n['id']; ?>">📝 コメントを見る</a></p>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <p><a href="menu.php">← メニューに戻る</a></p>
</body>
</html>
