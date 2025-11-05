<?php
session_start();
require 'db.php';

if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// 手動報酬一覧を取得（ユーザー名も含む）
$sql = "
SELECT
    m.user_id,
    u.name,
    m.month,
    m.override_amount,
    m.created_at
FROM manual_payments m
JOIN users u ON m.user_id = u.id
ORDER BY m.month DESC, u.name ASC
";

$stmt = $pdo->query($sql);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>手動報酬 修正履歴</title>
  <style>
    body { font-family: sans-serif; padding: 20px; }
    table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
    th, td { border: 1px solid #ccc; padding: 6px 10px; text-align: left; }
    th { background: #f0f0f0; }
    a { text-decoration: none; color: #007bff; }
    a:hover { text-decoration: underline; }
  </style>
</head>
<body>
  <h1>📝 手動報酬 修正履歴一覧</h1>

  <?php if (empty($records)): ?>
    <p>手動で修正された報酬はまだありません。</p>
  <?php else: ?>
    <table>
      <tr>
        <th>スタッフ名</th>
        <th>対象月</th>
        <th>修正金額</th>
        <th>修正日時</th>
        <th>操作</th>
      </tr>
      <?php foreach ($records as $r): ?>
        <tr>
          <td><?= htmlspecialchars($r['name']) ?></td>
          <td><?= htmlspecialchars($r['month']) ?></td>
          <td><?= number_format($r['override_amount']) ?>円</td>
          <td><?= htmlspecialchars($r['created_at']) ?></td>
          <td>
            <a href="pay_edit.php?user_id=<?= $r['user_id'] ?>&month=<?= $r['month'] ?>">編集</a> |
            <a href="pay_reset.php?user_id=<?= $r['user_id'] ?>&month=<?= $r['month'] ?>" onclick="return confirm('リセットしますか？');">リセット</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>

  <p><a href="menu.php">← メニューに戻る</a></p>
</body>
</html>
