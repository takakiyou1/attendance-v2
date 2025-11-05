<?php
session_start();
$_SESSION = []; // セッションの中身を空にする
session_destroy(); // セッションIDごと破棄

header('Location: login.php'); // ログイン画面に戻る
exit;
