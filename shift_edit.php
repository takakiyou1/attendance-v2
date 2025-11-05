<?php
session_start();
require 'db.php';

// ▼ 認証・管理者チェック
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// ▼ 編集対象のID確認
$id = $_GET['id'] ?? null;
if (!$id) {
    echo "不正なアクセスです。";
    exit;
}

// ▼ 対象のシフト取得
$stmt = $pdo->prepare("
    SELECT s.*, u.name FROM shifts s
    JOIN users u ON s.user_id = u.id
    WHERE s.id = ?
");
$stmt->execute([$id]);
$shift = $stmt->fetch();

if (!$shift) {
    echo "該当シフトが見つかりません。";
    exit;
}

$message = "";

// ▼ 更新処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shift_start = $_POST['shift_start'] ?? '';
    $shift_end = $_POST['shift_end'] ?? '';
    $hourly_wage = $_POST['hourly_wage'] ?? '';

    // 入力チェック
    if ($shift_start && $shift_end && is_numeric($hourly_wage)) {

        // ▼ 重複チェック（同じユーザー、同じ日、別のシフトID、時間が重なっていないか）
        $overlapCheck = $pdo->prepare("
            SELECT * FROM shifts
            WHERE user_id = ?
              AND date = ?
              AND id != ?
              AND (
                (shift_start < ? AND shift_end > ?) OR
                (shift_start < ? AND shift_end > ?) OR
                (shift_start >= ? AND shift_end <= ?)
              )
        ");
        $overlapCheck->execute([
            $shift['user_id'], $shift['date'], $id,
            $shift_end, $shift_end,
            $shift_start, $shift_start,
            $shift_start, $shift_end
        ]);

        if ($overlapCheck->rowCount() > 0) {
            $message = "指定された時間帯は、他のシフトと重複しています。";
        } else {
            // ▼ 更新処理
            $update = $pdo->prepare("
                UPDATE shifts
                SET shift_start = ?, shift_end = ?, hourly_wage = ?
                WHERE id = ?
            ");
            $update->execute([$shift_start, $shift_end, $hourly_wage, $id]);

            header("Location: shift_list.php");
            exit;
        }
    } else {
        $message = "すべての項目を正しく入力してください。";
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>シフト編集</title>
  <style>
    body { font-family: sans-serif; margin: 20px; }
    label { display: block; margin-top: 10px; }
    input { width: 100%; padding: 6px; margin-top: 4px; }
    button { margin-top: 20px; padding: 10px; width: 100%; }
    .error { color: red; margin-top: 10px; }
  </style>
</head>
<body>
  <h1>シフト編集</h1>

  <?php if (!empty($message)): ?>
    <p class="error"><?= htmlspecialchars($message) ?></p>
  <?php endif; ?>

  <p><strong>スタッフ名：</strong><?= htmlspecialchars($shift['name']) ?></p>
  <p><strong>日付：</strong><?= htmlspecialchars($shift['date']) ?></p>

  <form method="POST">
    <label>開始時間：
      <input type="time" name="shift_start" value="<?= htmlspecialchars($shift['shift_start']) ?>" required>
    </label>

    <label>終了時間：
      <input type="time" name="shift_end" value="<?= htmlspecialchars($shift['shift_end']) ?>" required>
    </label>

    <label>時給（円）：
      <input type="number" name="hourly_wage" value="<?= htmlspecialchars($shift['hourly_wage']) ?>" required>
    </label>

    <button type="submit">更新する</button>
  </form>

  <p><a href="shift_list.php">← シフト一覧に戻る</a></p>
</body>
</html>
