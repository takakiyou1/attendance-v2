<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>ログイン</title>
</head>
<body>
  <h1>ログイン画面</h1>

  <!-- エラーメッセージ表示 -->
  <?php if (!empty($_SESSION['error'])): ?>
    <p style="color: red;">
      <?php 
        echo $_SESSION['error']; 
        unset($_SESSION['error']); 
      ?>
    </p>
  <?php endif; ?>

  <form method="POST" action="authenticate.php">
    <label>メールアドレス：
      <input type="email" name="email" required>
    </label><br><br>

    <label>パスワード：
      <input type="password" name="password" required>
    </label><br><br>

    <button type="submit">ログイン</button>
  </form>
</body>
</html>
