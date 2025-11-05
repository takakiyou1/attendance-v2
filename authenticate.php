<?php
session_start();
require 'db.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// 入力チェック（空なら戻す）
if (empty($email) || empty($password)) {
    $_SESSION['error'] = 'メールアドレスとパスワードを入力してください。';
    header('Location: login.php');
    exit;
}

// データベースからユーザーを取得（メールで検索）
$sql = 'SELECT * FROM users WHERE email = ?';
$stmt = $pdo->prepare($sql);
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// パスワードを照合
if ($user && password_verify($password, $user['password'])) {
    // ログイン成功 → セッションに保存
    $_SESSION['user'] = $user;
    header('Location: menu.php');
    exit;
} else {
    // ログイン失敗
    $_SESSION['error'] = 'メールアドレスまたはパスワードが間違っています。';
    header('Location: login.php');
    exit;
}
