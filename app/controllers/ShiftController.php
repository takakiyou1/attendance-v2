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
     * My day view
     */
    public function my_day()
    {
        if (!Session::isLoggedIn()) {
            Response::redirect('/attendance-v2/public/index.php/login');
        }

        $date = $_GET['date'] ?? date('Y-m-d');
        $this->view('shift/my_day', ['date' => $date]);
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
     */
    private function formatShiftsForCalendar(array $shifts): array
    {
        $events = [];

        foreach ($shifts as $s) {
            $start = new DateTime("{$s['date']} {$s['shift_start']}");
            $end = new DateTime("{$s['date']} {$s['shift_end']}");
            if ($end <= $start) $end->modify('+1 day');

            $repeatMark = !empty($s['repeat_id']) ? '※繰り返し ' : '';

            $events[] = [
                'id' => $s['id'],
                'title' => $repeatMark . "{$s['user_name']}（{$s['shift_start']}〜{$s['shift_end']}）",
                'start' => $start->format('Y-m-d\TH:i:s'),
                'end' => $end->format('Y-m-d\TH:i:s'),
                'allDay' => false,
                'color' => $s['color'] ?? '#000000'
            ];
        }

        return $events;
    }

    /**
     * Format shifts for FullCalendar (my shifts)
     */
    private function formatMyShiftsForCalendar(array $shifts): array
    {
        $events = [];

        foreach ($shifts as $s) {
            $start = new DateTime("{$s['date']} {$s['shift_start']}");
            $end = new DateTime("{$s['date']} {$s['shift_end']}");
            if ($end <= $start) $end->modify('+1 day');

            // Format hour for display
            $startHour = (int)substr($s['shift_start'], 0, 2);
            $endHour = (int)substr($s['shift_end'], 0, 2);
            if ($startHour < 9) $startHour += 24;
            if ($endHour < 9) $endHour += 24;

            $startLabel = sprintf('%02d:%s', $startHour, substr($s['shift_start'], 3, 2));
            $endLabel = sprintf('%02d:%s', $endHour, substr($s['shift_end'], 3, 2));

            $events[] = [
                'title' => "{$startLabel}〜{$endLabel}",
                'start' => $start->format('Y-m-d\TH:i:s'),
                'end' => $end->format('Y-m-d\TH:i:s'),
                'allDay' => false,
                'color' => $s['color'] ?? '#3788d8'
            ];
        }

        return $events;
    }
}
