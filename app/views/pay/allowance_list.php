<?php
require __DIR__ . '/../../../config/database.php';

$user_id = $_GET['user_id'] ?? null;
$month   = $_GET['month'] ?? date('Y-m');
if (!$user_id) exit('不正なアクセスです。');

$stmt = $pdo->prepare("
  SELECT * FROM special_allowances
  WHERE user_id = ? AND month = ?
  ORDER BY id ASC
");
$stmt->execute([$user_id, $month]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$nameStmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
$nameStmt->execute([$user_id]);
$user = $nameStmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>特別手当一覧 | Re:time</title>
  <style>
    body { font-family: Meiryo, sans-serif; text-align:center; margin:40px; }
    table { border-collapse: collapse; width: 80%; margin: 20px auto; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
    th { background: #f2f2f2; }
    input, textarea { width:90%; padding:5px; }
    button { margin-top:10px; padding:6px 12px; border:none; border-radius:4px; cursor:pointer; }
    .save { background:#007bff; color:white; }
    .delete { background:#dc3545; color:white; }
  </style>
</head>
<body>
  <h1>特別手当一覧（<?= htmlspecialchars($user['name']) ?> / <?= htmlspecialchars($month) ?>）</h1>

  <form id="addForm" style="margin-bottom:20px;">
    <input type="hidden" name="user_id" value="<?= $user_id ?>">
    <input type="hidden" name="month" value="<?= $month ?>">
    <input type="text" name="title" placeholder="手当名（例：教育担当）" required>
    <input type="number" name="amount" placeholder="金額" required>
    <button type="submit" class="save">追加</button>
  </form>

  <table>
    <tr><th>ID</th><th>手当名</th><th>金額</th><th>操作</th></tr>
    <?php foreach ($rows as $r): ?>
    <tr>
      <td><?= $r['id'] ?></td>
      <td><?= htmlspecialchars($r['title']) ?></td>
      <td><?= number_format($r['amount']) ?> 円</td>
      <td>
        <button class="delete" onclick="deleteAllowance(<?= $r['id'] ?>)">削除</button>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>

  <p><a href="/attendance-v2/public/index.php/pay/summary?month=<?= $month ?>">← 報酬一覧へ戻る</a></p>

  <script>
  // ✅ 手当追加
  document.getElementById('addForm').addEventListener('submit', e => {
    e.preventDefault();
    fetch('/attendance-v2/api/save_special_allowance.php', {
      method: 'POST',
      body: new FormData(e.target)
    })
    .then(res => res.json())
    .then(res => {
      alert(res.message);
      location.reload();
    });
  });

  // ✅ 手当削除
  function deleteAllowance(id) {
    if (!confirm('この手当を削除しますか？')) return;
    fetch('/attendance-v2/api/delete_special_allowance.php', {
      method: 'POST',
      body: new URLSearchParams({ id })
    })
    .then(res => res.json())
    .then(res => {
      alert(res.message);
      location.reload();
    });
  }
  </script>
</body>
</html>
