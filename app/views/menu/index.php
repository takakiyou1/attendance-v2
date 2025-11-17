<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>メニュー</title>
  <style>
    body { font-family: Meiryo, sans-serif; text-align: center; background-color: #f9f9f9; margin-top: 80px; }
    h1 { color: #333; }
    ul { list-style: none; padding: 0; }
    li { margin: 10px 0; }
    a {
      display: inline-block;
      padding: 10px 20px;
      text-decoration: none;
      background-color: #007bff;
      color: white;
      border-radius: 5px;
      transition: 0.2s;
    }
    a:hover { background-color: #0056b3; }
  </style>
</head>
<body>
  <h1>ようこそ、<?= htmlspecialchars($_SESSION['user']['name']) ?> さん</h1>
  <h3>権限：<?= htmlspecialchars($_SESSION['user']['role']) === 'admin' ? '管理者' : 'スタッフ' ?></h3>

  <ul>
    <?php if ($_SESSION['user']['role'] === 'admin'): ?>
      <li><a href="/attendance-v2/public/user/list">👥 ユーザー管理</a></li>
      <li><a href="/attendance-v2/public/shift/calendar">📅 シフト管理</a></li>
      <li><a href="/attendance-v2/public/pay/summary">💰 報酬一覧</a></li>
      <li><a href="/attendance-v2/public/pay/settings">🧾 報酬設定一覧</a></li>
      <li><a href="/attendance-v2/public/news">📰 お知らせ</a></li>
    <?php else: ?>
      <li><a href="/attendance-v2/public/shift/view_my">📅 自分のシフト</a></li>
      <li><a href="/attendance-v2/public/shift/view_all">📋 全体シフト</a></li>
      <li><a href="/attendance-v2/public/news">📰 お知らせ</a></li>
      <li><a href="/attendance-v2/public/pay/my_pay">💰 自分の報酬</a></li>
    <?php endif; ?>
    <li><a href="/attendance-v2/public/logout">🚪 ログアウト</a></li>
  </ul>
</body>
</html>
