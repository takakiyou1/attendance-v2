<?php
require '../db.php';
header('Content-Type: application/json; charset=UTF-8');

$date = $_GET['date'] ?? '';

if (empty($date)) {
    echo json_encode(['error' => '日付が指定されていません']);
    exit;
}

try {
    $sql = "SELECT 
                s.id,
                s.user_id,
                u.name AS user_name,
                s.shift_start AS shift_start,
                s.shift_end AS shift_end
            FROM shifts s
            JOIN users u ON s.user_id = u.id
            WHERE DATE(s.shift_start) = ?
            ORDER BY s.shift_start ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$date]);
    $shifts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($shifts, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
