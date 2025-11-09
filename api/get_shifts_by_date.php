<?php
session_start();
// 旧: require '../db.php';
require __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=UTF-8');

$date = $_GET['date'] ?? '';
if (empty($date)) { echo json_encode(['error' => '日付が指定されていません']); exit; }

if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    echo json_encode([]); exit;
}

$stmt = $pdo->prepare("
  SELECT s.id, s.user_id, s.date, s.shift_start, s.shift_end, u.name
  FROM shifts s
  JOIN users u ON s.user_id = u.id
  WHERE s.date = ?
  ORDER BY s.shift_start ASC
");
$stmt->execute([$date]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
