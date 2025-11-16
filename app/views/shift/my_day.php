<?php
require __DIR__ . '/../../../config/database.php';

if (empty($_SESSION['user'])) {
  exit('ログインしてください');
}

$user_id = $_SESSION['user']['id'];
$name = $_SESSION['user']['name'];
$date = $_GET['date'] ?? date('Y-m-d');
$returnUrl = $_GET['return'] ?? '/attendance-v2/public/index.php/shift/view_my';

// ✅ Detect context: all shifts or my shifts
$isAllShifts = strpos($returnUrl, 'view_all') !== false;

if ($isAllShifts) {
  // Show all shifts with user names
  $stmt = $pdo->prepare("
    SELECT s.shift_start, s.shift_end, s.color, u.name as user_name
    FROM shifts s
    JOIN users u ON s.user_id = u.id
    WHERE s.date = ?
    ORDER BY s.shift_start ASC
  ");
  $stmt->execute([$date]);
} else {
  // Show only user's own shifts
  $stmt = $pdo->prepare("
    SELECT shift_start, shift_end, color
    FROM shifts
    WHERE user_id = ? AND date = ?
    ORDER BY shift_start ASC
  ");
  $stmt->execute([$user_id, $date]);
}

$shifts = $stmt->fetchAll(PDO::FETCH_ASSOC);
$count = count($shifts);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($date) ?> の<?= $isAllShifts ? '全体シフト' : '自分のシフト' ?></title>
<style>
body { font-family: Meiryo, sans-serif; background:#fafafa; text-align:center; margin:40px; }
h1 { margin-bottom:10px; }
p.info { color:#555; margin-bottom:20px; }
table {
  border-collapse: collapse;
  margin: 0 auto;
  width: 70%;
  background: white;
  box-shadow: 0 0 10px rgba(0,0,0,0.1);
  border-radius: 8px;
  overflow: hidden;
}
th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
th { background: #f2f2f2; }
tr:nth-child(even) { background: #f9f9f9; }
.color-box { display:inline-block; width:16px; height:16px; border:1px solid #999; margin-right:6px; vertical-align:middle; }
a { text-decoration: none; color: #007bff; }
a:hover { text-decoration: underline; }
</style>
</head>
<body>

<h1>📅 <?= htmlspecialchars($date) ?> の<?= $isAllShifts ? '全体シフト' : '自分のシフト' ?></h1>
<p class="info">登録シフト数：<?= $count ?> 件</p>

<?php if ($count === 0): ?>
  <p>この日には登録されたシフトはありません。</p>
<?php else: ?>
  <table>
    <?php if ($isAllShifts): ?>
      <tr><th>スタッフ</th><th>勤務時間</th><th>色</th></tr>
      <?php foreach ($shifts as $s): ?>
        <tr>
          <td><?= htmlspecialchars($s['user_name']) ?></td>
          <td><?= htmlspecialchars(substr($s['shift_start'], 0, 5)) ?>〜<?= htmlspecialchars(substr($s['shift_end'], 0, 5)) ?></td>
          <td><span class="color-box" style="background:<?= htmlspecialchars($s['color'] ?? '#000') ?>"></span><?= htmlspecialchars($s['color'] ?? '#000000') ?></td>
        </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr><th>勤務時間</th><th>色</th></tr>
      <?php foreach ($shifts as $s): ?>
        <tr>
          <td><?= htmlspecialchars(substr($s['shift_start'], 0, 5)) ?>〜<?= htmlspecialchars(substr($s['shift_end'], 0, 5)) ?></td>
          <td><span class="color-box" style="background:<?= htmlspecialchars($s['color'] ?? '#000') ?>"></span><?= htmlspecialchars($s['color'] ?? '#000000') ?></td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
  </table>
<?php endif; ?>

<p style="margin-top:30px;">
  <a href="<?= htmlspecialchars($returnUrl) ?>">← カレンダーに戻る</a>
</p>

</body>
</html>
