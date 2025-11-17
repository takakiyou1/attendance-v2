<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>報酬一覧 | Re:time</title>
  <style>
    table { border-collapse: collapse; width: 80%; margin: 20px auto; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
    th { background: #f2f2f2; }
    h1, h2 { text-align: center; }
    .month-nav { text-align: center; margin-bottom: 20px; }
    .month-nav a, .month-nav button {
      background: #007bff; color: #fff; padding: 6px 12px;
      margin: 0 5px; border-radius: 5px; text-decoration: none; border: none; cursor: pointer;
    }
    .month-nav a:hover, .month-nav button:hover { background: #0056b3; }
  </style>
</head>
<body>
  <h1>💰 報酬一覧（<?= htmlspecialchars($month) ?>）</h1>

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
  <tr>
    <th>スタッフ名</th>
    <th>支払い方式</th>
    <th>勤務時間</th>
    <th>報酬額</th>
    <th>特別手当</th> <!-- 追加 -->
    <th>合計支給額</th> <!-- 追加 -->
    <th>操作</th>
  </tr>
  <?php foreach ($data as $d): ?>
    <tr>
      <td><?= htmlspecialchars($d['name']) ?></td>
      <td><?= ($d['pay_type'] === 'fixed') ? '固定報酬' : '時給計算' ?></td>
      <td><?= round($d['total_hours'], 1) ?> 時間</td>
      <td><?= number_format($d['calculated_pay']) ?> 円</td>
      <td><?= number_format($d['special_allowance'] ?? 0) ?> 円</td> <!-- 追加 -->
      <td><strong><?= number_format($d['total_pay'] ?? $d['calculated_pay']) ?> 円</strong></td> <!-- 追加 -->
      <td>
        <a href="/attendance-v2/public/index.php/pay/edit?user_id=<?= $d['user_id'] ?>&month=<?= $month ?>">編集</a> |
        <a href="/attendance-v2/public/index.php/pay/allowance_list?user_id=<?= $d['user_id'] ?>&month=<?= $month ?>">手当一覧</a>
      </td>
    </tr>
  <?php endforeach; ?>
</table>


  <h2><a href="/attendance-v2/public/index.php/menu">← メニューに戻る</a></h2>
</body>
</html>
