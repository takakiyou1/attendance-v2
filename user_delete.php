<?php
session_start();
require 'db.php';

if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    exit('IDが指定されていません');
}

// 自分自身を削除しようとしていないかチェック
if ($id == $_SESSION['user']['id']) {
    exit('自分自身を削除することはできません');
}

$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$id]);

header('Location: user_list.php');
exit;
