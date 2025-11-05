<?php
session_start();
require 'db.php';

if (empty($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>メニュー</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body { font-family: sans-serif; padding: 20px; background-color: #f9f9f9; }
    h1 { margin-bottom: 5px; }
    h2 { margin-top: 30px; }
    ul { list-style: none; padding-left: 0; }
    li { margin: 6px 0; }
    a { text-decoration: none; color: #007bff; }
    a:hover { text-decoration: underline; }
  </style>
</head>
<body>
  <h1>ようこそ、<?= htmlspecialchars($user['name']) ?> さん</h1>
  <p>ログイン区分：<?= $user['role'] === 'admin' ? '管理者' : '従業員' ?></p>

  <!-- 管理者専用メニュー -->
  <?php if ($user['role'] === 'admin'): ?>
    <h2>📂 管理者メニュー</h2>
    <ul>
      <li><a href="shift_create.php">📅 単発シフト登録</a></li>
      <li><a href="shift_bulk_create.php">🔁 繰り返しシフト登録</a></li>
      <li><a href="shift_template_create.php">🧩 テンプレート登録</a></li>
      <li><a href="shift_alert_rule_create.php">⚠ アラート条件設定</a></li>
      <li><a href="shift_list.php">📋 シフト一覧（編集・削除）</a></li>
      <li><a href="shift_admin_calendar.php">📆 全体シフトカレンダー</a></li>
      <li><a href="user_list.php">👥 ユーザー管理</a></li>
      <li><a href="user_create.php">➕ ユーザー追加</a></li>
      <li><a href="pay_manual_list.php">📝 手動報酬修正履歴</a></li>
      <li><a href="shift_summary.php">💰 報酬一覧（日／月／年）</a></li>
    </ul>
  <?php endif; ?>

  <!-- 掲示板メニュー（共通） -->
  <h2>💬 掲示板</h2>
  <ul>
    <li><a href="news_board.php">📢 掲示板を読む</a></li>
    <li><a href="news_create.php">✏️ 新規投稿</a></li>
    <li><a href="news_manage.php">📄 投稿一覧（全件）</a></li>
    <li><a href="news_my_posts.php">🧍‍♂️ 自分の投稿履歴</a></li>
  </ul>

  <!-- 勤怠メニュー -->
  <h2>👤 自分の勤怠</h2>
  <ul>
    <li><a href="my_shift.php">📅 自分のシフト（リスト）</a></li>
    <li><a href="my_shift_calendar.php">📆 自分のシフト（カレンダー）</a></li>
    <li><a href="break_register.php">⏸️ 休憩登録</a></li>
  </ul>

  <p><a href="logout.php">🚪 ログアウト</a></p>
</body>
</html>
