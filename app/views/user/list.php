<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>ユーザー一覧</title>
  <style>
    table { border-collapse: collapse; margin: 20px auto; width: 90%; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
    th { background: #f0f0f0; }
    body { font-family: Meiryo, sans-serif; text-align: center; }
    a { text-decoration: none; color: #007bff; }
    a:hover { text-decoration: underline; }
    .add-btn {
      display: inline-block;
      margin: 20px;
      padding: 10px 20px;
      background-color: #28a745;
      color: white;
      border-radius: 5px;
      text-decoration: none;
    }
  </style>
</head>
<body>
  <h1>ユーザー一覧</h1>
  <a class="add-btn" href="/user/create">＋ 新規ユーザー追加</a>

  <table>
    <tr>
      <th>ID</th>
      <th>名前</th>
      <th>メール</th>
      <th>権限</th>
      <th>報酬タイプ</th>
      <th>金額</th>
      <th>操作</th>
    </tr>
    <?php foreach ($users as $u): ?>
      <tr>
        <td><?= htmlspecialchars($u['id']) ?></td>
        <td><?= htmlspecialchars($u['name']) ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td><?= htmlspecialchars($u['role']) ?></td>
        <td><?= htmlspecialchars($u['pay_type']) ?></td>
        <td><?= number_format($u['pay_rate']) ?></td>
        <td>
          <a href="/user/edit?id=<?= $u['id'] ?>">編集</a> |
          <a href="/user/delete?id=<?= $u['id'] ?>" onclick="return confirm('削除しますか？')">削除</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>

  <p><a href="/menu">← メニューに戻る</a></p>
</body>
</html>
