<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>お知らせ投稿</title>
  <style>
    body {
      font-family: Meiryo, sans-serif;
      margin: 40px;
      background-color: #f9f9f9;
    }
    .container {
      max-width: 800px;
      margin: 0 auto;
      background: white;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    h1 {
      color: #333;
      border-bottom: 3px solid #007bff;
      padding-bottom: 10px;
      margin-bottom: 30px;
    }
    .form-group {
      margin-bottom: 20px;
    }
    label {
      display: block;
      margin-bottom: 8px;
      font-weight: bold;
      color: #333;
    }
    input[type="text"],
    textarea {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 14px;
      font-family: Meiryo, sans-serif;
      box-sizing: border-box;
    }
    textarea {
      min-height: 200px;
      resize: vertical;
    }
    .btn {
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
      transition: 0.2s;
      font-size: 14px;
    }
    .btn-primary {
      background: #007bff;
      color: white;
    }
    .btn-primary:hover {
      background: #0056b3;
    }
    .btn-secondary {
      background: #6c757d;
      color: white;
    }
    .btn-secondary:hover {
      background: #545b62;
    }
    .button-group {
      display: flex;
      gap: 10px;
      margin-top: 30px;
    }
    @media (max-width: 768px) {
      body {
        margin: 20px;
      }
      .container {
        padding: 15px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>📝 お知らせ投稿</h1>

    <form id="newsForm">
      <div class="form-group">
        <label for="title">タイトル <span style="color:red;">*</span></label>
        <input type="text" id="title" name="title" required placeholder="タイトルを入力してください">
      </div>

      <div class="form-group">
        <label for="content">本文 <span style="color:red;">*</span></label>
        <textarea id="content" name="content" required placeholder="お知らせの内容を入力してください"></textarea>
      </div>

      <div class="button-group">
        <button type="submit" class="btn btn-primary">投稿する</button>
        <a href="/attendance-v2/public/index.php/news" class="btn btn-secondary">キャンセル</a>
      </div>
    </form>
  </div>

  <script>
    const form = document.getElementById('newsForm');

    form.addEventListener('submit', (e) => {
      e.preventDefault();

      const formData = new FormData(form);

      fetch('/attendance-v2/public/index.php/news/store', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(res => {
        if (res.status === 'success') {
          alert(res.message || 'お知らせを投稿しました');
          window.location.href = '/attendance-v2/public/index.php/news';
        } else {
          alert(res.message || '投稿に失敗しました');
        }
      })
      .catch(err => {
        console.error(err);
        alert('エラーが発生しました');
      });
    });
  </script>
</body>
</html>
