<?php
class ShiftController extends Controller
{
    // カレンダー（月間）表示
    public function calendar()
    {
        session_start();
        if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header('Location: /attendance-v2/public/index.php/login');
            exit;
        }
        $this->view('shift/calendar');
    }

    // 日別ガントチャート表示
    public function day()
    {
        session_start();
        if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header('Location: /attendance-v2/public/index.php/login');
            exit;
        }

        $date = $_GET['date'] ?? date('Y-m-d');
        $this->view('shift/day_gantt', ['date' => $date]);
    }

    // シフト登録・更新（単発）
    public function save()
    {
        require __DIR__ . '/../../config/database.php';

        $id          = $_POST['id'] ?? null;
        $user_id     = $_POST['user_id'] ?? null;
        $date        = $_POST['date'] ?? null;
        $shift_start = $_POST['shift_start'] ?? null;
        $shift_end   = $_POST['shift_end'] ?? null;

        // --- 入力バリデーション ---
        if (!$user_id || !$date || !$shift_start || !$shift_end) {
            echo json_encode(['status' => 'error', 'message' => '必須項目が入力されていません。']);
            exit;
        }

        // --- 重複チェック ---
        $sql = "
            SELECT COUNT(*) FROM shifts
            WHERE user_id = ?
              AND date = ?
              AND id != ?
              AND (
                (shift_start < ? AND shift_end > ?) OR
                (shift_start < ? AND shift_end > ?) OR
                (shift_start >= ? AND shift_end <= ?)
              )
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $user_id, $date, $id ?: 0,
            $shift_end, $shift_start,
            $shift_start, $shift_end,
            $shift_start, $shift_end
        ]);
        $overlap = $stmt->fetchColumn();

        if ($overlap > 0) {
            echo json_encode(['status' => 'error', 'message' => 'このスタッフは同時間帯に別シフトがあります。']);
            exit;
        }

        // --- 登録・更新処理 ---
        if ($id) {
            $stmt = $pdo->prepare("UPDATE shifts SET user_id=?, date=?, shift_start=?, shift_end=? WHERE id=?");
            $stmt->execute([$user_id, $date, $shift_start, $shift_end, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO shifts (user_id, date, shift_start, shift_end) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $date, $shift_start, $shift_end]);
        }

        echo json_encode(['status' => 'success']);
    }

    // 繰り返し登録処理
    public function save_repeat()
    {
        require __DIR__ . '/../../config/database.php';

        $user_id     = $_POST['user_id'] ?? null;
        $date        = $_POST['date'] ?? null;
        $shift_start = $_POST['shift_start'] ?? null;
        $shift_end   = $_POST['shift_end'] ?? null;
        $repeat_type = $_POST['repeat_type'] ?? null;
        $repeat_end  = $_POST['repeat_end'] ?? null;
        $days        = $_POST['days'] ?? [];

        if (!$user_id || !$date || !$shift_start || !$shift_end || !$repeat_type || !$repeat_end) {
            echo json_encode(['status'=>'error','message'=>'繰り返し設定が不十分です']);
            exit;
        }

        // 繰り返しグループIDを生成
        $repeat_id = 'REP_' . date('Ymd_His') . '_' . rand(100,999);

        $current_date = new DateTime($date);
        $end_date = new DateTime($repeat_end);
        $count = 0;

        while ($current_date <= $end_date) {
            // 曜日指定対応（weekly時のみ）
            if ($repeat_type === 'weekly' && !in_array($current_date->format('N'), $days)) {
                $current_date->modify('+1 day');
                continue;
            }

            // --- 重複チェック ---
            $sql = "
                SELECT COUNT(*) FROM shifts
                WHERE user_id = ?
                  AND date = ?
                  AND (
                    (shift_start < ? AND shift_end > ?) OR
                    (shift_start < ? AND shift_end > ?) OR
                    (shift_start >= ? AND shift_end <= ?)
                  )
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $user_id, $current_date->format('Y-m-d'),
                $shift_end, $shift_start,
                $shift_start, $shift_end,
                $shift_start, $shift_end
            ]);
            $overlap = $stmt->fetchColumn();

            if ($overlap == 0) {
                $stmt = $pdo->prepare("
                    INSERT INTO shifts (user_id, date, shift_start, shift_end, repeat_id)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$user_id, $current_date->format('Y-m-d'), $shift_start, $shift_end, $repeat_id]);
                $count++;
            }

            // 日付を進める
            if ($repeat_type === 'daily') {
                $current_date->modify('+1 day');
            } elseif ($repeat_type === 'weekly') {
                $current_date->modify('+1 week');
            } elseif ($repeat_type === 'monthly') {
                $current_date->modify('+1 month');
            } else {
                break;
            }
        }

        echo json_encode(['status'=>'success','message'=>"{$count}件のシフトを登録しました（グループID: {$repeat_id}）"]);
    }

    // シフト削除
    public function delete()
    {
        require __DIR__ . '/../../config/database.php';

        $id = $_POST['id'] ?? null;
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM shifts WHERE id = ?");
            $stmt->execute([$id]);
        }

        echo json_encode(['status' => 'deleted']);
    }
}
