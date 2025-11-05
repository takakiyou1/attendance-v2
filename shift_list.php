<?php
session_start();
require 'db.php';

// 管理者チェック
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// シフト取得（ユーザー名付き）
$shifts_stmt = $pdo->query("
    SELECT s.*, u.name 
    FROM shifts s 
    JOIN users u ON s.user_id = u.id 
    ORDER BY s.date DESC, s.shift_start ASC
");
$shifts = $shifts_stmt->fetchAll();

// アラートルール取得
$rules_stmt = $pdo->query("SELECT * FROM shift_alert_rules WHERE enabled = 1");
$rules = $rules_stmt->fetchAll();

// 日別でシフトをグループ化
$grouped_shifts = [];
foreach ($shifts as $s) {
    $grouped_shifts[$s['date']][] = $s;
}

// 日別アラート検出
$alerts_by_date = [];

foreach ($rules as $rule) {
    $rule_days = array_map('intval', explode(',', $rule['days_of_week']));
    $start = $rule['time_start'];
    $end = $rule['time_end'];

    foreach ($grouped_shifts as $date => $shift_list) {
        $day_of_week = date('w', strtotime($date));
        if (!in_array($day_of_week, $rule_days)) continue;

        $count = 0;
        foreach ($shift_list as $s) {
            if (
                ($s['shift_start'] <= $end && $s['shift_end'] >= $start) ||
                ($start > $end) // 深夜帯サポート（22:00〜05:00など）
            ) {
                $count++;
            }
        }

        if ($count < $rule['required_count']) {
            $alerts_by_date[$date][] = "{$rule['label']}：{$count}人（不足）";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>シフト一覧（管理者）</title>
  <style>
    table { border-collapse: collapse; width: 100%; margin-bottom: 30px; }
    th, td { border: 1px solid #ccc; padding: 6px; text-align: center; }
    th { background: #eee; }
    .alert { color: red; font-weight: bold; }
  </style>
</head>
<body>
  <h1>全シフト一覧（管理者）</h1>

  <p>
    <a href="shift_create.php">📅 単発登録</a> |
    <a href="shift_bulk_create.php">🔁 繰り返し登録</a> |
    <a href="shift_template_create.php">🧩 テンプレ管理</a> |
    <a href="shift_alert_rule_create.php">⚠ アラート条件</a>
  </p>

  <?php foreach ($grouped_shifts as $date => $shift_list): ?>
    <h2><?php echo htmlspecialchars($date); ?>
      <?php if (!empty($alerts_by_date[$date])): ?>
        <span class="alert">
          <?php foreach ($alerts_by_date[$date] as $a): ?>
            [⚠ <?php echo htmlspecialchars($a); ?>]
          <?php endforeach; ?>
        </span>
      <?php endif; ?>
    </h2>

    <table>
      <thead>
        <tr>
          <th>名前</th>
          <th>開始</th>
          <th>終了</th>
          <th>時給</th>
          <th>操作</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($shift_list as $s): ?>
          <tr>
            <td><?php echo htmlspecialchars($s['name']); ?></td>
            <td><?php echo htmlspecialchars(substr($s['shift_start'], 0, 5)); ?></td>
            <td><?php echo htmlspecialchars(substr($s['shift_end'], 0, 5)); ?></td>
            <td><?php echo htmlspecialchars($s['hourly_wage']); ?> 円</td>
            <td>
              <a href="shift_edit.php?id=<?php echo $s['id']; ?>">編集</a> |
              <a href="shift_delete.php?id=<?php echo $s['id']; ?>" onclick="return confirm('本当に削除しますか？');">削除</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endforeach; ?>

  <p><a href="menu.php">← メニューに戻る</a></p>
</body>
</html>
