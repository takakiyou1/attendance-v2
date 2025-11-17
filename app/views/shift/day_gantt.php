<?php
require __DIR__ . '/../../../config/database.php';
$date = $_GET['date'] ?? date('Y-m-d');

// シフト取得（開始時間順）
$stmt = $pdo->prepare("
  SELECT s.id, u.name, s.shift_start, s.shift_end, s.color
  FROM shifts s
  JOIN users u ON s.user_id = u.id
  WHERE s.date = ?
  ORDER BY s.shift_start ASC
");
$stmt->execute([$date]);
$shifts = $stmt->fetchAll(PDO::FETCH_ASSOC);
$count = count($shifts);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($date) ?> のシフト一覧</title>
<style>
body { font-family: Meiryo, sans-serif; background:#fafafa; text-align:center; margin:40px; }
h1 { margin-bottom:10px; }
p.info { color:#555; margin-bottom:20px; }
table {
  border-collapse: collapse;
  margin: 0 auto;
  width: 80%;
  background: white;
  box-shadow: 0 0 10px rgba(0,0,0,0.1);
  border-radius: 8px;
  overflow: hidden;
}
th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
th { background: #f2f2f2; }
tr:nth-child(even) { background: #f9f9f9; }
.color-box {
  display:inline-block;
  width:16px; height:16px;
  border:1px solid #999;
  margin-right:6px;
  vertical-align:middle;
}
a { text-decoration: none; color: #007bff; }
a:hover { text-decoration: underline; }
</style>
</head>
<body>

<h1>📅 <?= htmlspecialchars($date) ?> のシフト一覧</h1>
<p class="info">出勤スタッフ数：<?= $count ?> 名</p>

<?php if ($count === 0): ?>
  <p>この日には登録されたシフトはありません。</p>
<?php else: ?>
  <table>
    <tr>
      <th>スタッフ名</th>
      <th>勤務時間</th>
      <th>色</th>
    </tr>
    <?php foreach ($shifts as $s): ?>
      <tr>
        <td><?= htmlspecialchars($s['name']) ?></td>
        <td><?= htmlspecialchars(substr($s['shift_start'],0,5)) ?>〜<?= htmlspecialchars(substr($s['shift_end'],0,5)) ?></td>
        <td>
          <span class="color-box" style="background:<?= htmlspecialchars($s['color'] ?? '#000') ?>"></span>
          <?= htmlspecialchars($s['color'] ?? '#000000') ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
<?php endif; ?>

<p style="margin-top:30px;">
  <a href="/attendance-v2/public/index.php/shift/calendar">← カレンダーに戻る</a>
</p>

</body>
</html>
