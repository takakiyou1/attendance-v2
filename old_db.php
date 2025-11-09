<?php
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=attendance_v2;charset=utf8',
        'root',
        'root' // ← MAMPのデフォルト。XAMPPなら空文字 ''
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'データベース接続エラー: ' . $e->getMessage();
    exit;
}
?>
