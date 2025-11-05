<?php
session_start();
require 'db.php';

// ログインチェック
if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// 削除対象ID取得
$id = $_GET['id'] ?? null;
if (!$id) {
    echo "不正なアクセスです。";
    exit;
}

// ニュースが存在するか確認
$stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
$stmt->execute([$id]);
$news = $stmt->fetch();

if (!$news) {
    echo "該当ニュースが見つかりません。";
    exit;
}

// 削除処理
$delete = $pdo->prepare("DELETE FROM news WHERE id = ?");
$delete->execute([$id]);

// 一覧に戻る
header("Location: news_manage.php");
exit;
