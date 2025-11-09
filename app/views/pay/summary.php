<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>報酬集計</title>
  <style>
    table { border-collapse: collapse; width: 80%; margin: 0 auto; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
    th { background-color: #f2f2f2; }
    h1, h2 { text-align: center; }
    body { font-family: Meiryo, sans-serif; margin-top: 50px; }
  </style>
</head>
<body>
  <h1>報酬一覧（<?= htmlspecialchars($month) ?>）</h1>

  <table>
    <tr>
      <th>スタッフ名</th>
      <th>支払い方式</th>
      <th>勤務時間</th>
      <th>報酬額</th>
    </tr>
    <?php foreach ($data as $d): ?>
      <tr>
        <td><?= htmlspecialchars($d['name']) ?></td>
        <td><?= htmlspecialchars($d['pay_type_display']) ?></td>
        <td><?= round($d['total_hours'], 1) ?> 時間</td>
        <td><?= number_format($d['calculated_pay']) ?> 円</td>
      </tr>
    <?php endforeach; ?>
  </table>

  <h2><a href="/attendance-v2/public/index.php/menu">← メニューに戻る</a></h2>
</body>
</html>
