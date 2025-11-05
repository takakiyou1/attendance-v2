<?php
session_start();
require 'db.php';

// 管理者チェック
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$message = '';

// 曜日名と番号の対応
$day_labels = ['日','月','火','水','木','金','土'];

// ユーザー取得（時給制のみ）
$users = $pdo->query("SELECT id, name FROM users WHERE pay_type = 'hourly' ORDER BY name")->fetchAll();

// テンプレート取得
$templates = $pdo->query("SELECT * FROM shift_templates ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $start_date = $_POST['start_date'];
    $shift_start = $_POST['shift_start'];
    $shift_end = $_POST['shift_end'];
    $weeks = (int)$_POST['weeks'];
    $selected_days = $_POST['days'] ?? [];
    $overwrite = !empty($_POST['overwrite']);

    if (!$user_id || !$start_date || !$shift_start || !$shift_end || !$weeks || empty($selected_days)) {
        $message = '全ての項目を入力してください。';
    } else {
        $stmt = $pdo->prepare("SELECT pay_rate FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if (!$user) {
            $message = 'ユーザーが見つかりません。';
        } else {
            $count = 0;
            $base = new DateTime($start_date);

            for ($w = 0; $w < $weeks; $w++) {
                foreach ($selected_days as $day_num) {
                    $date = clone $base;
                    $date->modify("+{$w} week");
                    $date->modify(($day_num - $date->format('w')) . ' days');
                    $date_str = $date->format('Y-m-d');

                    $check = $pdo->prepare("SELECT COUNT(*) FROM shifts WHERE user_id = ? AND date = ?");
                    $check->execute([$user_id, $date_str]);
                    $exists = $check->fetchColumn();

                    if ($exists == 0) {
                        // 新規登録
                        $insert = $pdo->prepare("
                            INSERT INTO shifts (user_id, date, shift_start, shift_end, hourly_wage)
                            VALUES (?, ?, ?, ?, ?)
                        ");
                        $insert->execute([$user_id, $date_str, $shift_start, $shift_end, $user['pay_rate']]);
                        $count++;
                    } elseif ($overwrite) {
                        // 上書き
                        $update = $pdo->prepare("
                            UPDATE shifts SET shift_start = ?, shift_end = ?, hourly_wage = ?
                            WHERE user_id = ? AND date = ?
                        ");
                        $update->execute([$shift_start, $shift_end, $user['pay_rate'], $user_id, $date_str]);
                        $count++;
                    }
                }
            }

            $message = "シフトを {$count} 件登録しました。";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>繰り返しシフト登録</title>
</head>
<body>
  <h1>繰り返しシフト登録（管理者）</h1>

  <?php if ($message): ?>
    <p style="color: green;"><?php echo htmlspecialchars($message); ?></p>
  <?php endif; ?>

  <form method="POST">
    <label>ユーザー：
      <select name="user_id" required>
        <option value="">選択してください</option>
        <?php foreach ($users as $u): ?>
          <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['name']); ?></option>
        <?php endforeach; ?>
      </select>
    </label><br><br>

    <label>テンプレートから読み込む：
      <select id="template_selector">
        <option value="">-- 選択してください --</option>
        <?php foreach ($templates as $tpl): ?>
          <option value="<?php echo $tpl['id']; ?>"
            data-days="<?php echo $tpl['days']; ?>"
            data-start="<?php echo $tpl['shift_start']; ?>"
            data-end="<?php echo $tpl['shift_end']; ?>"
            data-weeks="<?php echo $tpl['default_weeks']; ?>"
          >
            <?php echo htmlspecialchars($tpl['name']); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label><br><br>

    <label>開始日（最初の週）：
      <input type="date" name="start_date" required>
    </label><br><br>

    <label>勤務時間：</label>
    <input type="time" name="shift_start" required> 〜
    <input type="time" name="shift_end" required><br><br>

    <label>繰り返す週数：
      <input type="number" name="weeks" min="1" max="12" value="4" required> 週間
    </label><br><br>

    <label>対象曜日：</label><br>
    <?php foreach ($day_labels as $i => $label): ?>
      <label>
        <input type="checkbox" name="days[]" value="<?php echo $i; ?>"> <?php echo $label; ?>
      </label>
    <?php endforeach; ?>
    <br><br>

    <label>
      <input type="checkbox" name="overwrite" value="1"> 既存のシフトを上書きする
    </label><br><br>

    <button type="submit">登録する</button>
  </form>

  <p><a href="shift_list.php">← シフト一覧に戻る</a></p>

  <script>
    document.getElementById('template_selector').addEventListener('change', function () {
      const selected = this.options[this.selectedIndex];
      if (!selected.value) return;

      const days = selected.dataset.days.split(',').map(d => d.trim());
      const start = selected.dataset.start;
      const end = selected.dataset.end;
      const weeks = selected.dataset.weeks;

      // チェックボックスリセット＆再適用
      document.querySelectorAll('input[name="days[]"]').forEach(cb => {
        cb.checked = days.includes(cb.value);
      });

      document.querySelector('input[name="shift_start"]').value = start;
      document.querySelector('input[name="shift_end"]').value = end;
      document.querySelector('input[name="weeks"]').value = weeks;
    });
  </script>
</body>
</html>
