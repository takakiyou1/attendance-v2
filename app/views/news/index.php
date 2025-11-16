<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>お知らせ</title>
  <style>
    body {
      font-family: Meiryo, sans-serif;
      margin: 40px;
      background-color: #f9f9f9;
    }
    .container {
      max-width: 900px;
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
    .header-actions {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
    }
    .btn {
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
      transition: 0.2s;
    }
    .btn-primary {
      background: #007bff;
      color: white;
    }
    .btn-primary:hover {
      background: #0056b3;
    }
    .btn-danger {
      background: #dc3545;
      color: white;
    }
    .btn-danger:hover {
      background: #c82333;
    }
    .btn-secondary {
      background: #6c757d;
      color: white;
    }
    .btn-secondary:hover {
      background: #545b62;
    }
    .news-item {
      border: 1px solid #ddd;
      border-radius: 5px;
      padding: 20px;
      margin-bottom: 20px;
      background: #fafafa;
    }
    .news-item h2 {
      margin: 0 0 10px 0;
      color: #333;
      font-size: 1.3em;
    }
    .news-meta {
      color: #666;
      font-size: 0.9em;
      margin-bottom: 15px;
    }
    .news-content {
      line-height: 1.6;
      color: #444;
      white-space: pre-wrap;
    }
    .news-actions {
      margin-top: 15px;
      padding-top: 15px;
      border-top: 1px solid #ddd;
    }
    .no-news {
      text-align: center;
      padding: 40px;
      color: #999;
      font-size: 1.1em;
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
    <div class="header-actions">
      <h1>📢 お知らせ</h1>
      <div>
        <?php if (in_array($_SESSION['user']['role'], ['admin', 'poster'])): ?>
          <a href="/attendance-v2/public/index.php/news/create" class="btn btn-primary">新規投稿</a>
        <?php endif; ?>
        <a href="/attendance-v2/public/index.php/menu" class="btn btn-secondary">メニューに戻る</a>
      </div>
    </div>

    <?php if (empty($news)): ?>
      <div class="no-news">
        お知らせはまだありません
      </div>
    <?php else: ?>
      <?php foreach ($news as $item): ?>
        <div class="news-item">
          <h2><?= htmlspecialchars($item['title']) ?></h2>
          <div class="news-meta">
            投稿者: <?= htmlspecialchars($item['author_name'] ?? '不明') ?> |
            投稿日時: <?= htmlspecialchars($item['created_at']) ?>
          </div>
          <div class="news-content"><?= htmlspecialchars($item['content']) ?></div>

          <?php if ($_SESSION['user']['role'] === 'admin'): ?>
            <div class="news-actions">
              <button class="btn btn-danger" onclick="deleteNews(<?= $item['id'] ?>)">削除</button>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <script>
    function deleteNews(id) {
      if (!confirm('このお知らせを削除しますか？')) return;

      fetch('/attendance-v2/public/index.php/news/delete', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({ id })
      })
      .then(res => res.json())
      .then(res => {
        if (res.status === 'success') {
          alert(res.message || 'お知らせを削除しました');
          location.reload();
        } else {
          alert(res.message || '削除に失敗しました');
        }
      })
      .catch(err => {
        console.error(err);
        alert('エラーが発生しました');
      });
    }
  </script>
</body>
</html>
