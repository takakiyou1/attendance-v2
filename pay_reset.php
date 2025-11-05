<?php
session_start();
require 'db.php';

if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$user_id = $_GET['user_id'] ?? null;
$month = $_GET['month'] ?? null;

if (!$user_id || !$month) {
    exit('不正なリクエストです');
}

// 手動報酬削除
$stmt = $pdo->prepare("DELETE FROM manual_payments WHERE user_id = ? AND month = ?");
$stmt->execute([$user_id, $month]);

header("Location: shift_summary.php");
exit;
