<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($title) ?></title>
  <style>
    body {
      font-family: "Hiragino Kaku Gothic ProN", Meiryo, sans-serif;
      background-color: #f9f9f9;
      color: #333;
      text-align: center;
      margin-top: 100px;
    }
    h1 {
      color: #0066cc;
      margin-bottom: 20px;
    }
    p {
      font-size: 18px;
      margin-top: 10px;
    }
    a {
      display: inline-block;
      margin-top: 40px;
      padding: 10px 20px;
      background-color: #007bff;
      color: white;
      text-decoration: none;
      border-radius: 5px;
      transition: 0.3s;
    }
    a:hover {
      background-color: #0056b3;
    }
  </style>
</head>
<body>
  <h1><?= htmlspecialchars($title) ?></h1>
  <p><?= htmlspecialchars($message) ?></p>

  <a href="/attendance-v2/public/index.php/hello">Helloページへ →</a>
</body>
</html>
