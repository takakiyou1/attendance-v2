<?php
session_start();
require 'db.php';

// 管理者チェック
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// 更新対象のID
$id = $_POST['id'] ?? null;
if (!$id) {
    echo "無効なアクセスです。";
    exit;
}

// 入力された値を取得
$clock_in     = $_POST['clock_in']     ?: null;
$break_start  = $_POST['break_start']  ?: null;
$break_end    = $_POST['break_end']    ?: null;
$clock_out    = $_POST['clock_out']    ?: null;

// DB更新処理
$sql = "UPDATE attendances SET 
            clock_in = ?, 
            break_start = ?, 
            break_end = ?, 
            clock_out = ?
        WHERE id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$clock_in, $break_start, $break_end, $clock_out, $id]);

// リダイレクト
header('Location: admin_history.php');
exit;
