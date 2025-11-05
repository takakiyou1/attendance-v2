<?php
session_start();
require 'db.php';

// 管理者チェック
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// ID確認
$id = $_GET['id'] ?? null;
if (!$id) {
    echo "不正なアクセスです。";
    exit;
}

// 該当シフトが存在するか確認（任意）
$stmt = $pdo->prepare("SELECT * FROM shifts WHERE id = ?");
$stmt->execute([$id]);
$shift = $stmt->fetch();

if (!$shift) {
    echo "該当シフトが見つかりません。";
    exit;
}

// 削除処理
$delete = $pdo->prepare("DELETE FROM shifts WHERE id = ?");
$delete->execute([$id]);

// 一覧へリダイレクト
header("Location: shift_list.php");
exit;
