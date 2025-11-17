<?php
require __DIR__ . '/../../../config/database.php';

$user_id = $_GET['user_id'] ?? null;
if (!$user_id) exit('不正なアクセスです。');

$stmt = $pdo->prepare("
  SELECT * FROM pay_rate_history
  WHERE user_id = ?
  ORDER BY start_date DESC
");
$stmt->execute([$user_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ユーザー名を取得
$nameStmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
$nameStmt->execute([$user_id]);
$user = $nameStmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>報酬履歴</title>
  <style>
    table { border-collapse: collapse; width: 80%; margin: 40px auto; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
    th { background: #f2f2f2; }
    h1 { text-align:center; }
  </style>
</head>
<body>
  <h1>報酬履歴（<?= htmlspecialchars($user['name']) ?> さん）</h1>

  <table>
    <tr>
      <th>支払い方式</th>
      <th>報酬金額</th>
      <th>適用開始日</th>
      <th>適用終了日</th>
    </tr>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= $r['pay_type'] === 'fixed' ? '固定' : '時給' ?></td>
        <td><?= number_format($r['pay_rate']) ?> 円</td>
        <td><?= htmlspecialchars($r['start_date']) ?></td>
        <td><?= $r['end_date'] ? htmlspecialchars($r['end_date']) : '現在有効' ?></td>
      </tr>
    <?php endforeach; ?>
  </table>

  <p style="text-align:center;">
    <a href="/attendance-v2/public/pay/settings">← 報酬設定一覧へ戻る</a>
  </p>
</body>
</html>
