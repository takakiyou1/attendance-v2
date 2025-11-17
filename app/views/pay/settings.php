<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>報酬設定一覧 | Re:time</title>
<style>
table { border-collapse: collapse; width: 80%; margin: 40px auto; }
th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
th { background: #f2f2f2; }
h1 { text-align: center; }
a { color: #007bff; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>
</head>
<body>
  <h1>🧾 報酬設定一覧</h1>
  <table>
    <tr>
      <th>ID</th>
      <th>スタッフ名</th>
      <th>支払い方式</th>
      <th>報酬金額</th>
      <th>操作</th>
    </tr>
    <?php foreach ($users as $u): ?>
      <tr>
        <td><?= htmlspecialchars($u['id']) ?></td>
        <td><?= htmlspecialchars($u['name']) ?></td>
        <td><?= $u['pay_type'] === 'fixed' ? '固定' : '時給' ?></td>
        <td><a href="/attendance-v2/public/index.php/pay/history?user_id=<?= $u['id'] ?>"><?= number_format($u['pay_rate']) ?> 円</a></td>
        <td><a href="/attendance-v2/public/index.php/pay/setting_edit?user_id=<?= $u['id'] ?>">編集</a></td>
      </tr>
    <?php endforeach; ?>
  </table>

  <p style="text-align:center;"><a href="/attendance-v2/public/index.php/menu">← メニューに戻る</a></p>
</body>
</html>
