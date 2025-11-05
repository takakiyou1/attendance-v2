<?php
session_start();
require 'db.php';

if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$mode = $_GET['mode'] ?? 'month'; // 'day', 'month', or 'year'

// 手動報酬一覧
$manual_stmt = $pdo->query("SELECT user_id, month, override_amount FROM manual_payments");
$manual_data = [];
foreach ($manual_stmt->fetchAll(PDO::FETCH_ASSOC) as $m) {
    $manual_data[$m['user_id']][$m['month']] = $m['override_amount'];
}

// 勤務データ取得（date単位）
$sql = "
SELECT
    u.id AS user_id,
    u.name,
    u.pay_type,
    u.pay_rate,
    s.date,
    TIMESTAMPDIFF(MINUTE, s.shift_start, s.shift_end)
        - IFNULL((
            SELECT SUM(TIMESTAMPDIFF(MINUTE, b.break_start, b.break_end))
            FROM breaks b
            WHERE b.user_id = s.user_id AND b.date = s.date
        ), 0) AS net_minutes
FROM shifts s
JOIN users u ON s.user_id = u.id
WHERE s.date < CURDATE()
ORDER BY s.date DESC, u.name
";
$rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// 集計処理
$summary = [];

foreach ($rows as $r) {
    $user_id = $r['user_id'];
    $name = $r['name'];
    $date = $r['date'];
    $pay_type = $r['pay_type'];
    $pay_rate = $r['pay_rate'];
    $minutes = (int)$r['net_minutes'];

    $yyyymm = date('Y-m', strtotime($date));
    $yyyy = date('Y', strtotime($date));
    $key = ($mode === 'day') ? $date : (($mode === 'year') ? $yyyy : $yyyymm);

    // 自動報酬
    $auto_reward = ($pay_type === 'hourly') ? round(($minutes / 60) * $pay_rate) : $pay_rate;

    // 手動報酬（月単位にしか存在しない）
    $manual_override = ($mode === 'month' && isset($manual_data[$user_id][$yyyymm]))
        ? $manual_data[$user_id][$yyyymm] : null;

    $reward = $manual_override ?? $auto_reward;
    $is_manual = !is_null($manual_override);

    $summary[$key][$user_id]['name'] = $name;
    $summary[$key][$user_id]['pay_type'] = $pay_type;
    $summary[$key][$user_id]['pay_rate'] = $pay_rate;
    $summary[$key][$user_id]['reward'] = ($summary[$key][$user_id]['reward'] ?? 0) + $reward;
    $summary[$key][$user_id]['is_manual'] = $is_manual || ($summary[$key][$user_id]['is_manual'] ?? false);
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>報酬一覧（<?= htmlspecialchars($mode) ?>別）</title>
  <style>
    body { font-family: sans-serif; padding: 20px; }
    h1 { margin-bottom: 10px; }
    h2 { margin-top: 30px; }
    select { padding: 4px 8px; }
    table { border-collapse: collapse; width: 100%; margin-bottom: 30px; }
    th, td { border: 1px solid #ccc; padding: 6px 10px; text-align: left; }
    th { background: #f0f0f0; }
    .manual { color: green; font-weight: bold; }
    a { text-decoration: none; color: #007bff; }
    a:hover { text-decoration: underline; }
  </style>
</head>
<body>
  <h1>💰 報酬一覧（<?= $mode === 'day' ? '日別' : ($mode === 'year' ? '年別' : '月別') ?>）</h1>

  <form method="get" style="margin-bottom: 20px;">
    <label>表示形式：
      <select name="mode" onchange="this.form.submit()">
        <option value="day" <?= $mode === 'day' ? 'selected' : '' ?>>日別</option>
        <option value="month" <?= $mode === 'month' ? 'selected' : '' ?>>月別</option>
        <option value="year" <?= $mode === 'year' ? 'selected' : '' ?>>年別</option>
      </select>
    </label>
  </form>

  <?php foreach ($summary as $period => $users): ?>
    <h2>📅 <?= $period ?> <?= $mode === 'month' ? '月' : ($mode === 'year' ? '年' : '') ?></h2>
    <table>
      <tr>
        <th>スタッフ名</th>
        <th>報酬種別</th>
        <th>単価</th>
        <th>報酬金額</th>
        <?php if ($mode === 'month'): ?><th>操作</th><?php endif; ?>
      </tr>
      <?php foreach ($users as $user_id => $u): ?>
        <tr>
          <td><?= htmlspecialchars($u['name']) ?></td>
          <td><?= $u['pay_type'] === 'hourly' ? '時給' : '固定' ?></td>
          <td><?= number_format($u['pay_rate']) ?>円</td>
          <td>
            <strong><?= number_format($u['reward']) ?>円</strong>
            <?= $u['is_manual'] ? '<span class="manual">（編集済）</span>' : '' ?>
          </td>
          <?php if ($mode === 'month'): ?>
            <td>
              <a href="pay_edit.php?user_id=<?= $user_id ?>&month=<?= $period ?>">編集</a>
              <?php if ($u['is_manual']): ?>
                | <a href="pay_reset.php?user_id=<?= $user_id ?>&month=<?= $period ?>" onclick="return confirm('手動報酬をリセットしますか？');">リセット</a>
              <?php endif; ?>
            </td>
          <?php endif; ?>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endforeach; ?>

  <p><a href="menu.php">← メニューに戻る</a></p>
</body>
</html>
