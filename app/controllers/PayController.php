<?php
class PayController extends Controller
{
    // 📊 報酬一覧（修正版：履歴テーブル対応）
    public function summary()
    {
        if (empty($_SESSION['user'])) {
            header('Location: /attendance-v2/public/index.php/login');
            exit;
        }

        require __DIR__ . '/../../config/database.php';

        // 表示する月
        $month = $_GET['month'] ?? date('Y-m');

        // ✅ 1. 報酬履歴をもとにシフト集計
        // Note: For overnight shifts (e.g., 22:00-03:00), we add 24 hours if shift_end < shift_start
        $sql = "
            SELECT
                s.user_id,
                u.name,
                h.pay_type,
                h.pay_rate,
                DATE_FORMAT(s.date, '%Y-%m') AS month,
                SUM(
                    CASE
                        WHEN s.shift_end < s.shift_start
                        THEN TIME_TO_SEC(TIMEDIFF(s.shift_end, s.shift_start)) / 3600 + 24
                        ELSE TIME_TO_SEC(TIMEDIFF(s.shift_end, s.shift_start)) / 3600
                    END
                ) AS total_hours,
                CASE
                    WHEN h.pay_type = 'fixed' THEN h.pay_rate
                    ELSE ROUND(SUM(
                        CASE
                            WHEN s.shift_end < s.shift_start
                            THEN TIME_TO_SEC(TIMEDIFF(s.shift_end, s.shift_start)) / 3600 + 24
                            ELSE TIME_TO_SEC(TIMEDIFF(s.shift_end, s.shift_start)) / 3600
                        END
                    ) * h.pay_rate)
                END AS calculated_pay
            FROM shifts s
            JOIN users u ON s.user_id = u.id
            JOIN pay_rate_history h
              ON h.user_id = s.user_id
              AND s.date BETWEEN h.start_date AND COALESCE(h.end_date, '9999-12-31')
            WHERE DATE_FORMAT(s.date, '%Y-%m') = ?
            GROUP BY s.user_id, u.name, h.pay_type, h.pay_rate, month
            ORDER BY u.id ASC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$month]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ✅ 2. 手当を取得（special_allowances）
        $stmt2 = $pdo->prepare("
            SELECT user_id, SUM(amount) AS total_allowance
            FROM special_allowances
            WHERE month = ?
            GROUP BY user_id
        ");
        $stmt2->execute([$month]);
        $allowances = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        // 手当データを連想配列化
        $allowanceMap = [];
        foreach ($allowances as $a) {
            $allowanceMap[$a['user_id']] = $a['total_allowance'];
        }

        // ✅ 3. 手当を加算
        foreach ($data as &$d) {
            $allowance = $allowanceMap[$d['user_id']] ?? 0;
            $d['special_allowance'] = $allowance;
            $d['total_pay'] = $d['calculated_pay'] + $allowance;
        }

        // ✅ 4. ビューへ送る
        $this->view('pay/summary', [
            'month' => $month,
            'data' => $data
        ]);
    }


    // ✏️ 報酬編集ページ
    public function edit()
    {
        if (empty($_SESSION['user'])) {
            header('Location: /attendance-v2/public/index.php/login');
            exit;
        }

        require __DIR__ . '/../../config/database.php';

        $user_id = $_GET['user_id'] ?? null;
        $month   = $_GET['month'] ?? date('Y-m');
        if (!$user_id) exit('不正なアクセスです');

        // スタッフ情報＋自動計算額・上書き額を取得
        // Note: For overnight shifts (e.g., 22:00-03:00), we add 24 hours if shift_end < shift_start
        $stmt = $pdo->prepare("
            SELECT
                u.id AS user_id,
                u.name,
                u.pay_type,
                u.pay_rate,
                COALESCE(SUM(
                    CASE
                        WHEN s.shift_end < s.shift_start
                        THEN TIME_TO_SEC(TIMEDIFF(s.shift_end, s.shift_start)) / 3600 + 24
                        ELSE TIME_TO_SEC(TIMEDIFF(s.shift_end, s.shift_start)) / 3600
                    END
                ), 0) AS total_hours,
                CASE
                    WHEN u.pay_type = 'fixed' THEN u.pay_rate
                    ELSE ROUND(SUM(
                        CASE
                            WHEN s.shift_end < s.shift_start
                            THEN TIME_TO_SEC(TIMEDIFF(s.shift_end, s.shift_start)) / 3600 + 24
                            ELSE TIME_TO_SEC(TIMEDIFF(s.shift_end, s.shift_start)) / 3600
                        END
                    ) * u.pay_rate)
                END AS auto_amount,
                m.override_amount AS manual_amount
            FROM users u
            LEFT JOIN shifts s
              ON u.id = s.user_id AND DATE_FORMAT(s.date, '%Y-%m') = ?
            LEFT JOIN manual_payments m
              ON u.id = m.user_id AND m.month = ?
            WHERE u.id = ?
            GROUP BY u.id, u.name, u.pay_type, u.pay_rate, m.override_amount
        ");
        $stmt->execute([$month, $month, $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->view('pay/edit', [
            'user' => $user,
            'month' => $month,
            'user_id' => $user_id
        ]);
    }

    // 🧾 報酬設定一覧
    public function settings()
    {
        if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header('Location: /attendance-v2/public/index.php/login');
            exit;
        }

        require __DIR__ . '/../../config/database.php';

        $stmt = $pdo->query("
            SELECT id, name, pay_type, pay_rate
            FROM users
            ORDER BY id ASC
        ");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('pay/settings', ['users' => $users]);
    }

    // ✏️ 個別報酬設定編集ページ
    public function setting_edit()
    {
        if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header('Location: /attendance-v2/public/index.php/login');
            exit;
        }

        $user_id = $_GET['user_id'] ?? null;
        if (!$user_id) exit('不正なアクセスです');

        require __DIR__ . '/../../config/database.php';
        $stmt = $pdo->prepare("SELECT id, name, pay_type, pay_rate FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->view('pay/setting_edit', ['user' => $user]);
    }

    // 📜 報酬履歴ページ
    public function history()
    {
        if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header('Location: /attendance-v2/public/index.php/login');
            exit;
        }

        require __DIR__ . '/../../config/database.php';
        $user_id = $_GET['user_id'] ?? null;
        if (!$user_id) exit('不正なアクセスです');
        $this->view('pay/history', ['user_id' => $user_id]);
    }

    // 💰 手当一覧ページ
    public function allowance_list()
    {
        if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header('Location: /attendance-v2/public/index.php/login');
            exit;
        }

        require __DIR__ . '/../../config/database.php';
        $user_id = $_GET['user_id'] ?? null;
        $month   = $_GET['month'] ?? date('Y-m');
        if (!$user_id) exit('不正なアクセスです');

        $this->view('pay/allowance_list', [
            'user_id' => $user_id,
            'month' => $month
        ]);
    }

    // 👤 スタッフ用マイページ（報酬確認）
    public function my_pay()
    {
        if (empty($_SESSION['user'])) {
            header('Location: /attendance-v2/public/index.php/login');
            exit;
        }

        // スタッフ本人の情報
        $user_id = $_SESSION['user']['id'];
        $name    = $_SESSION['user']['name'];
        $month   = $_GET['month'] ?? date('Y-m');

        require __DIR__ . '/../../config/database.php';

        // ✅ 1. 基本報酬（履歴に基づく）
        // Note: For overnight shifts (e.g., 22:00-03:00), we add 24 hours if shift_end < shift_start
        $sql = "
            SELECT
                SUM(
                    CASE
                        WHEN s.shift_end < s.shift_start
                        THEN TIME_TO_SEC(TIMEDIFF(s.shift_end, s.shift_start)) / 3600 + 24
                        ELSE TIME_TO_SEC(TIMEDIFF(s.shift_end, s.shift_start)) / 3600
                    END
                ) AS total_hours,
                h.pay_type,
                h.pay_rate,
                CASE
                    WHEN h.pay_type = 'fixed' THEN h.pay_rate
                    ELSE ROUND(SUM(
                        CASE
                            WHEN s.shift_end < s.shift_start
                            THEN TIME_TO_SEC(TIMEDIFF(s.shift_end, s.shift_start)) / 3600 + 24
                            ELSE TIME_TO_SEC(TIMEDIFF(s.shift_end, s.shift_start)) / 3600
                        END
                    ) * h.pay_rate)
                END AS base_pay
            FROM shifts s
            JOIN pay_rate_history h
              ON h.user_id = s.user_id
              AND s.date BETWEEN h.start_date AND COALESCE(h.end_date, '9999-12-31')
            WHERE s.user_id = ? AND DATE_FORMAT(s.date, '%Y-%m') = ?
            GROUP BY h.pay_type, h.pay_rate
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $month]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $base_pay = $row['base_pay'] ?? 0;
        $total_hours = $row['total_hours'] ?? 0;
        $pay_type = $row['pay_type'] ?? '';
        $pay_rate = $row['pay_rate'] ?? 0;

        // ✅ 2. 手当を取得
        $stmt2 = $pdo->prepare("
            SELECT title, amount 
            FROM special_allowances 
            WHERE user_id = ? AND month = ?
            ORDER BY id ASC
        ");
        $stmt2->execute([$user_id, $month]);
        $allowances = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        $allowance_sum = array_sum(array_column($allowances, 'amount'));

        // ✅ 3. 合計
        $total_pay = $base_pay + $allowance_sum;

        // ✅ 4. ビューへ
        $this->view('pay/my_pay', [
            'name' => $name,
            'month' => $month,
            'total_hours' => $total_hours,
            'base_pay' => $base_pay,
            'pay_type' => $pay_type,
            'pay_rate' => $pay_rate,
            'allowances' => $allowances,
            'total_pay' => $total_pay
        ]);
    }
}
