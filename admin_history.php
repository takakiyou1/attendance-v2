<?php
session_start();
require 'db.php';

// 管理者チェック
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];

// ▼ 検索条件取得
$selected_user_id = $_GET['user_id'] ?? '';
$selected_date = $_GET['date'] ?? '';

// ▼ ユーザー一覧取得（セレクトボックス用）
$users_stmt = $pdo->query("SELECT id, name FROM users ORDER BY name");
$users = $users_stmt->fetchAll();

// ▼ 勤怠履歴取得クエリ構築
$sql = "
  SELECT a.*, u.name 
  FROM attendances a 
  JOIN users u ON a.user_id = u.id 
  WHERE 1 = 1
";

$params = [];

if (!empty($selected_user_id)) {
    $sql .= " AND a.user_id = ?";
    $params[] = $selected_user_id;
}

if (!empty($selected_date)) {
    $sql .= " AND a.date = ?";
    $params[] = $selected_date;
}

$sql .= " ORDER BY a.date DESC, a.user_id";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$records = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>勤怠履歴（管理者）</title>
  <style>
    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
    form { margin-bottom: 20px; }
  </style>
</head>
<body>
  <h1>勤怠履歴（管理者）</h1>
  <p>ようこそ管理者 <?php echo htmlspecialchars($user['name']); ?> さん</p>

  <form method="GET">
    <label>ユーザー：
      <select name="user_id">
        <option value="">-- 全員 --</option>
        <?php foreach ($users as $u): ?>
          <option value="<?php echo $u['id']; ?>" <?php if ($selected_user_id == $u['id']) echo 'selected'; ?>>
            <?php echo htmlspecialchars($u['name']); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>

    <label>日付：
      <input type="date" name="date" value="<?php echo htmlspecialchars($selected_date); ?>">
    </label>

    <button type="submit">検索</button>
    <a href="admin_history.php">リセット</a>
  </form>

  <table>
    <thead>
      <tr>
        <th>ユーザー名</th>
        <th>日付</th>
        <th>出勤</th>
        <th>休憩開始</th>
        <th>休憩終了</th>
        <th>退勤</th>
        <th>操作</th> <!-- 編集列 -->
      </tr>
    </thead>
    <tbody>
      <?php foreach ($records as $row): ?>
        <tr>
          <td><?php echo htmlspecialchars($row['name']); ?></td>
          <td><?php echo htmlspecialchars($row['date']); ?></td>
          <td><?php echo htmlspecialchars($row['clock_in'] ?? ''); ?></td>
          <td><?php echo htmlspecialchars($row['break_start'] ?? ''); ?></td>
          <td><?php echo htmlspecialchars($row['break_end'] ?? ''); ?></td>
          <td><?php echo htmlspecialchars($row['clock_out'] ?? ''); ?></td>
          <td>
            <a href="edit_attendance.php?id=<?php echo $row['id']; ?>">編集</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <p><a href="menu.php">← メニューに戻る</a></p>
</body>
</html>
