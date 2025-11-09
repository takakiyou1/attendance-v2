<?php
class PayController extends Controller
{
    public function summary()
    {
        session_start();
        if (empty($_SESSION['user'])) {
            header('Location: /attendance-v2/public/index.php/login');
            exit;
        }

        require __DIR__ . '/../../config/database.php';

        // 表示する月を指定（例：2025-11）
        $month = $_GET['month'] ?? date('Y-m');

        // 1. シフト情報を取得
      $sql = "
    SELECT 
        s.user_id,
        u.name,
        u.pay_type,
        u.pay_rate,
        DATE_FORMAT(s.date, '%Y-%m') AS month,
        SUM(
            TIME_TO_SEC(
                TIMEDIFF(s.shift_end, s.shift_start)
            ) / 3600
        ) AS total_hours
    FROM 
        shifts s
    JOIN 
        users u ON s.user_id = u.id
    WHERE 
        DATE_FORMAT(s.date, '%Y-%m') = ?
    GROUP BY 
        s.user_id, 
        u.name, 
        u.pay_type, 
        u.pay_rate, 
        month
        ";


        $stmt = $pdo->prepare($sql);
        $stmt->execute([$month]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. 手動修正を取得
        $manuals = $pdo->prepare("SELECT user_id, override_amount FROM manual_payments WHERE month = ?");
        $manuals->execute([$month]);
        $manualMap = [];
        foreach ($manuals->fetchAll(PDO::FETCH_ASSOC) as $m) {
            $manualMap[$m['user_id']] = $m['override_amount'];
        }

        // 3. 報酬を計算（時給 or 固定 or 手動優先）
        foreach ($data as &$row) {
            $userId = $row['user_id'];

            if (isset($manualMap[$userId])) {
                $row['calculated_pay'] = $manualMap[$userId]; // 手動上書き
                $row['pay_type_display'] = '手動上書き';
            } elseif ($row['pay_type'] === 'fixed') {
                $row['calculated_pay'] = $row['pay_rate']; // 固定報酬
                $row['pay_type_display'] = '固定報酬';
            } else {
                $row['calculated_pay'] = round($row['total_hours'] * $row['pay_rate']);
                $row['pay_type_display'] = '時給計算';
            }
        }

        $this->view('pay/summary', [
            'month' => $month,
            'data' => $data
        ]);
    }
}
