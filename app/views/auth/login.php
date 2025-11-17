<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>ログイン | Re:time</title>
  <style>
    body {
      font-family: "Hiragino Kaku Gothic ProN", Meiryo, sans-serif;
      background-color: #f9f9f9;
      text-align: center;
      margin-top: 100px;
    }
    .logo {
      font-size: 48px;
      font-weight: bold;
      color: #0066cc;
      margin-bottom: 10px;
      letter-spacing: 2px;
    }
    .tagline {
      color: #666;
      margin-bottom: 40px;
      font-size: 14px;
    }
    form {
      display: inline-block;
      background: white;
      padding: 40px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    input {
      margin: 10px 0;
      padding: 8px;
      width: 250px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }
    button {
      padding: 10px 20px;
      background-color: #0066cc;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
    button:hover {
      background-color: #004d99;
    }
    .error {
      color: red;
      margin-bottom: 15px;
    }
  </style>
</head>
<body>
  <div class="logo">Re:time</div>
  <p class="tagline">シフト・勤怠管理システム</p>

  <?php if (!empty($error)): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>

  <form method="POST" action="<?= url('login') ?>">
    <input type="email" name="email" placeholder="メールアドレス" required><br>
    <input type="password" name="password" placeholder="パスワード" required><br>
    <button type="submit">ログイン</button>
  </form>
</body>
</html>
