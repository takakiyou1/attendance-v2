<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>自分の報酬</title>
<style>
body { font-family: Meiryo, sans-serif; text-align:center; background:#fafafa; margin:40px; }
h1, h2 { text-align:center; }
table { border-collapse: collapse; width: 80%; margin: 20px auto; }
th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
th { background: #f2f2f2; }
.total { font-weight: bold; background: #e9f7ef; }
.month-nav { text-align: center; margin-bottom: 20px; }
.month-nav a, .month-nav button {
  background: #007bff; color: #fff; padding: 6px 12px;
  margin: 0 5px; border-radius: 5px; text-decoration: none; border: none; cursor: pointer;
}
.month-nav a:hover, .month-nav button:hover { background: #0056b3; }
</style>
</head>
<body>
  <h1>💰 <?= htmlspecialchars($name) ?> さんの報酬（<?= htmlspecialchars($month) ?>）</h1>

  <div class="month-nav">
    <?php
      $prevMonth = date('Y-m', strtotime($month . ' -1 month'));
      $nextMonth = date('Y-m', strtotime($month . ' +1 month'));
    ?>
    <a href="?month=<?= $prevMonth ?>">← 前月</a>
    <a href="?month=<?= $nextMonth ?>">次月 →</a>

    <form method="GET" action="" style="display:inline;">
      <input type="month" name="month" value="<?= $month ?>">
      <button type="submit">表示</button>
    </form>
  </div>

  <table>
    <tr><th>勤務時間</th><td><?= round($total_hours,1) ?> 時間</td></tr>
    <tr><th>支払い方式</th><td><?= ($pay_type === 'fixed') ? '固定報酬' : '時給制（'.number_format($pay_rate).'円）' ?></td></tr>
    <tr><th>基本報酬</th><td><?= number_format($base_pay) ?> 円</td></tr>
  </table>

  <h2>特別手当</h2>
  <table>
    <tr><th>手当名</th><th>金額</th></tr>
    <?php if (empty($allowances)): ?>
      <tr><td colspan="2">手当はありません。</td></tr>
    <?php else: ?>
      <?php foreach ($allowances as $a): ?>
        <tr>
          <td><?= htmlspecialchars($a['title']) ?></td>
          <td><?= number_format($a['amount']) ?> 円</td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
    <tr class="total">
      <th>合計支給額</th>
      <td><?= number_format($total_pay) ?> 円</td>
    </tr>
  </table>

  <h2><a href="/attendance-v2/public/menu">← メニューに戻る</a></h2>
</body>
</html>
