<?php
/**
 * Shift Controller
 * Handles shift management and scheduling
 */
require_once __DIR__ . '/../Models/Shift.php';
require_once __DIR__ . '/../Models/User.php';

class ShiftController extends Controller
{
    private Shift $shiftModel;
    private User $userModel;

    public function __construct() {
        $this->shiftModel = new Shift();
        $this->userModel = new User();
    }

    // ========================================
    // View Methods (Admin)
    // ========================================

    /**
     * Admin calendar view
     */
    public function calendar()
    {
        if (!Session::isAdmin()) {
            Response::redirect('/attendance-v2/public/index.php/login');
        }

        $this->view('shift/calendar');
    }

    /**
     * Day view for admin
     */
    public function day()
    {
        if (!Session::isAdmin()) {
            Response::redirect('/attendance-v2/public/index.php/login');
        }

        $date = $_GET['date'] ?? date('Y-m-d');
        $this->view('shift/day_gantt', ['date' => $date]);
    }

    // ========================================
    // View Methods (Staff)
    // ========================================

    /**
     * View all shifts (read-only)
     */
    public function view_all()
    {
        if (!Session::isLoggedIn()) {
            Response::redirect('/attendance-v2/public/index.php/login');
        }

        $this->view('shift/view_all');
    }

    /**
     * View my shifts (read-only)
     */
    public function view_my()
    {
        if (!Session::isLoggedIn()) {
            Response::redirect('/attendance-v2/public/index.php/login');
        }

        $this->view('shift/view_my');
    }

    /**
     * My day view - shows shifts for a specific date
     * Can show all shifts or just user's shifts depending on return URL
     */
    public function my_day()
    {
        // Debug log
        error_log("my_day called - GET params: " . print_r($_GET, true));

        if (!Session::isLoggedIn()) {
            Response::redirect('/attendance-v2/public/index.php/login');
        }

        $date = $_GET['date'] ?? date('Y-m-d');
        $returnType = $_GET['return'] ?? 'view_my';

        // Debug log
        error_log("my_day - date: $date, returnType: $returnType");
        $user_id = $_SESSION['user']['id'];

        // Detect context: all shifts or my shifts based on return type
        $isAllShifts = ($returnType === 'view_all');

        // Build full return URL for view
        $returnUrl = '/attendance-v2/public/index.php/shift/' . $returnType;

        // Fetch shifts - load database connection
        require __DIR__ . '/../../config/database.php';

        if ($isAllShifts) {
            // Show all shifts with user names
            $stmt = $pdo->prepare("
                SELECT shifts.shift_start, shifts.shift_end, shifts.color, users.name as user_name
                FROM shifts
                JOIN users ON shifts.user_id = users.id
                WHERE shifts.date = ?
                ORDER BY shifts.shift_start ASC
            ");
            $stmt->execute([$date]);
        } else {
            // Show only user's own shifts
            $stmt = $pdo->prepare("
                SELECT shift_start, shift_end, color
                FROM shifts
                WHERE user_id = ? AND date = ?
                ORDER BY shift_start ASC
            ");
            $stmt->execute([$user_id, $date]);
        }

        $shifts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('shift/my_day', [
            'date' => $date,
            'returnUrl' => $returnUrl,
            'isAllShifts' => $isAllShifts,
            'shifts' => $shifts,
            'count' => count($shifts)
        ]);
    }

    // ========================================
    // CRUD Operations
    // ========================================

    /**
     * Save shift (create or update)
     */
    public function save()
    {
        $id          = $_POST['id'] ?? null;
        $user_id     = $_POST['user_id'] ?? null;
        $date        = $_POST['date'] ?? null;
        $shift_start = $_POST['shift_start'] ?? null;
        $shift_end   = $_POST['shift_end'] ?? null;
        $color       = $_POST['color'] ?? null;

        // Validation
        if (!$user_id || !$date || !$shift_start || !$shift_end) {
            Response::error('必須項目が入力されていません。');
        }

        try {
            $data = [
                'user_id' => $user_id,
                'date' => $date,
                'shift_start' => $shift_start,
                'shift_end' => $shift_end
            ];

            if ($color) {
                $data['color'] = $color;
            }

            if ($id) {
                // Update
                $this->shiftModel->updateShift($id, $data);
            } else {
                // Create
                $this->shiftModel->createShift($data);
            }

            Response::success([], 'シフトを保存しました');
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    /**
     * Save repeated shifts
     */
    public function save_repeat()
    {
        $user_id     = $_POST['user_id'] ?? null;
        $date        = $_POST['date'] ?? null;
        $shift_start = $_POST['shift_start'] ?? null;
        $shift_end   = $_POST['shift_end'] ?? null;
        $repeat_type = $_POST['repeat_type'] ?? null;
        $repeat_end  = $_POST['repeat_end'] ?? null;
        $days        = $_POST['days'] ?? [];
        $color       = $_POST['color'] ?? null;

        // Validation
        if (!$user_id || !$date || !$shift_start || !$shift_end || !$repeat_type || !$repeat_end) {
            Response::error('繰り返し設定が不十分です');
        }

        try {
            $data = [
                'user_id' => $user_id,
                'date' => $date,
                'shift_start' => $shift_start,
                'shift_end' => $shift_end,
                'repeat_type' => $repeat_type,
                'repeat_end' => $repeat_end,
                'days' => $days
            ];

            if ($color) {
                $data['color'] = $color;
            }

            $result = $this->shiftModel->createRepeated($data);

            Response::success($result, "{$result['count']}件のシフトを登録しました（グループID: {$result['repeat_id']}）");
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    /**
     * Delete shift
     */
    public function delete()
    {
        $id = $_POST['id'] ?? null;

        if (!$id) {
            Response::error('IDが指定されていません');
        }

        try {
            $this->shiftModel->delete($id);
            Response::success([], 'シフトを削除しました');
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    // ========================================
    // API Methods
    // ========================================

    /**
     * API: Get all shifts with user info
     */
    public function apiAll()
    {
        if (!Session::isLoggedIn()) {
            Response::json(['error' => 'no_session'], 401);
        }

        try {
            $shifts = $this->shiftModel->getAllWithUsers();
            $events = $this->formatShiftsForCalendar($shifts);
            Response::json($events);
        } catch (Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * API: Get my shifts
     */
    public function apiMy()
    {
        if (!Session::isLoggedIn()) {
            Response::json(['error' => 'no_session'], 401);
        }

        try {
            $userId = Session::userId();
            $shifts = $this->shiftModel->getByUser($userId);
            $events = $this->formatMyShiftsForCalendar($shifts);
            Response::json($events);
        } catch (Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * API: Get shift by ID
     */
    public function apiById()
    {
        if (!Session::isLoggedIn()) {
            Response::json(['error' => 'no_session'], 401);
        }

        $id = $_GET['id'] ?? null;
        if (!$id) {
            Response::error('IDが指定されていません', 400);
        }

        try {
            $shift = $this->shiftModel->getWithUser($id);
            if (!$shift) {
                Response::error('シフトが見つかりません', 404);
            }
            Response::json($shift);
        } catch (Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * API: Get shifts by date
     */
    public function apiByDate()
    {
        if (!Session::isLoggedIn()) {
            Response::json(['error' => 'no_session'], 401);
        }

        $date = $_GET['date'] ?? date('Y-m-d');

        try {
            $shifts = $this->shiftModel->getByDate($date);
            Response::json($shifts);
        } catch (Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * API: Delete shift group
     */
    public function apiDeleteGroup()
    {
        if (!Session::isAdmin()) {
            Response::error('権限がありません', 403);
        }

        $repeatId = $_POST['repeat_id'] ?? null;
        if (!$repeatId) {
            Response::error('repeat_idが指定されていません', 400);
        }

        try {
            $count = $this->shiftModel->deleteByRepeatId($repeatId);
            Response::success(['count' => $count], "{$count}件のシフトを削除しました");
        } catch (Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    /**
     * API: Update shift group
     */
    public function apiUpdateGroup()
    {
        if (!Session::isAdmin()) {
            Response::error('権限がありません', 403);
        }

        $repeatId = $_POST['repeat_id'] ?? null;
        if (!$repeatId) {
            Response::error('repeat_idが指定されていません', 400);
        }

        $data = [];
        if (isset($_POST['user_id'])) $data['user_id'] = $_POST['user_id'];
        if (isset($_POST['shift_start'])) $data['shift_start'] = $_POST['shift_start'];
        if (isset($_POST['shift_end'])) $data['shift_end'] = $_POST['shift_end'];

        if (empty($data)) {
            Response::error('更新するデータがありません', 400);
        }

        try {
            $count = $this->shiftModel->updateByRepeatId($repeatId, $data);
            Response::success(['count' => $count], "{$count}件のシフトを更新しました");
        } catch (Exception $e) {
            Response::error($e->getMessage(), 500);
        }
    }

    // ========================================
    // Helper Methods
    // ========================================

    /**
     * Format shifts for FullCalendar (all shifts)
     * Handles overnight shifts for 09:00-29:00 workday window
     * Uses extended hour format (e.g., 27:00 instead of next day 03:00)
     */
    private function formatShiftsForCalendar(array $shifts): array
    {
        $events = [];

        foreach ($shifts as $s) {
            // Parse shift times
            $startParts = explode(':', $s['shift_start']);
            $endParts = explode(':', $s['shift_end']);
            $startHour = (int)$startParts[0];
            $startMinute = (int)$startParts[1];
            $startSecond = isset($startParts[2]) ? (int)$startParts[2] : 0;
            $endHour = (int)$endParts[0];
            $endMinute = (int)$endParts[1];
            $endSecond = isset($endParts[2]) ? (int)$endParts[2] : 0;

            // Start time is straightforward
            $start = new DateTime("{$s['date']} {$s['shift_start']}");
            $startStr = $start->format('Y-m-d\TH:i:s');

            // For overnight shifts, use extended hours (24+) on the SAME date
            $isOvernightShift = ($endHour < $startHour) || ($endHour < 9 && $startHour >= 9);

            if ($isOvernightShift) {
                // Add 24 to end hour to get extended format (e.g., 03:00 becomes 27:00)
                $extendedEndHour = $endHour + 24;
                $endStr = sprintf('%sT%02d:%02d:%02d', $s['date'], $extendedEndHour, $endMinute, $endSecond);
            } else {
                // Regular shift - use normal end time
                $end = new DateTime("{$s['date']} {$s['shift_end']}");
                $endStr = $end->format('Y-m-d\TH:i:s');
            }

            $repeatMark = !empty($s['repeat_id']) ? '※繰り返し ' : '';

            $events[] = [
                'id' => $s['id'],
                'title' => $repeatMark . "{$s['user_name']}（{$s['shift_start']}〜{$s['shift_end']}）",
                'start' => $startStr,
                'end' => $endStr,
                'allDay' => false,
                'color' => $s['color'] ?? '#000000'
            ];
        }

        return $events;
    }

    /**
     * Format shifts for FullCalendar (my shifts)
     * Handles overnight shifts for 09:00-29:00 workday window
     * Uses extended hour format (e.g., 27:00 instead of next day 03:00)
     */
    private function formatMyShiftsForCalendar(array $shifts): array
    {
        $events = [];

        foreach ($shifts as $s) {
            // Parse shift times
            $startParts = explode(':', $s['shift_start']);
            $endParts = explode(':', $s['shift_end']);
            $startHour = (int)$startParts[0];
            $startMinute = (int)$startParts[1];
            $startSecond = isset($startParts[2]) ? (int)$startParts[2] : 0;
            $endHour = (int)$endParts[0];
            $endMinute = (int)$endParts[1];
            $endSecond = isset($endParts[2]) ? (int)$endParts[2] : 0;

            // Start time is straightforward
            $start = new DateTime("{$s['date']} {$s['shift_start']}");
            $startStr = $start->format('Y-m-d\TH:i:s');

            // For overnight shifts, use extended hours (24+) on the SAME date
            $isOvernightShift = ($endHour < $startHour) || ($endHour < 9 && $startHour >= 9);

            if ($isOvernightShift) {
                // Add 24 to end hour to get extended format (e.g., 03:00 becomes 27:00)
                $extendedEndHour = $endHour + 24;
                $endStr = sprintf('%sT%02d:%02d:%02d', $s['date'], $extendedEndHour, $endMinute, $endSecond);
            } else {
                // Regular shift - use normal end time
                $end = new DateTime("{$s['date']} {$s['shift_end']}");
                $endStr = $end->format('Y-m-d\TH:i:s');
            }

            // Format display labels for 24+ hour format (e.g., 27:00 instead of 03:00)
            $startLabel = $startHour < 9 ? sprintf('%02d:%02d', $startHour + 24, $startMinute) : sprintf('%02d:%02d', $startHour, $startMinute);
            $endLabel = $endHour < 9 ? sprintf('%02d:%02d', $endHour + 24, $endMinute) : sprintf('%02d:%02d', $endHour, $endMinute);

            $events[] = [
                'title' => "{$startLabel}〜{$endLabel}",
                'start' => $startStr,
                'end' => $endStr,
                'allDay' => false,
                'color' => $s['color'] ?? '#3788d8'
            ];
        }

        return $events;
    }
}
