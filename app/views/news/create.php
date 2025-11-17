<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>お知らせ投稿</title>
  <link rel="stylesheet" href="/attendance-v2/public/index.php/css/modern-design.css">
</head>
<body>
  <!-- Header -->
  <div class="page-header">
    <div class="container-narrow">
      <div class="page-header-content">
        <h1 class="page-title">
          📝 お知らせ投稿
        </h1>
        <div class="page-actions">
          <a href="/attendance-v2/public/index.php/news" class="btn btn-secondary">
            ← 一覧に戻る
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="container-narrow">
    <div class="card">
      <form id="newsForm">
        <div class="form-group">
          <label class="form-label" for="title">
            タイトル <span style="color: var(--color-error);">*</span>
          </label>
          <input
            type="text"
            id="title"
            name="title"
            class="form-input"
            required
            placeholder="タイトルを入力してください"
          >
        </div>

        <div class="form-group">
          <label class="form-label" for="content">
            本文 <span style="color: var(--color-error);">*</span>
          </label>
          <textarea
            id="content"
            name="content"
            class="form-textarea"
            required
            placeholder="お知らせの内容を入力してください"
            style="min-height: 300px;"
          ></textarea>
          <span class="form-hint">Markdownは使用できません。改行はそのまま表示されます。</span>
        </div>

        <div style="display: flex; gap: var(--space-sm); margin-top: var(--space-xl);">
          <button type="submit" class="btn btn-primary" style="flex: 1;">
            📤 投稿する
          </button>
          <a href="/attendance-v2/public/index.php/news" class="btn btn-outline" style="flex: 1;">
            キャンセル
          </a>
        </div>
      </form>
    </div>
  </div>

  <script>
    const form = document.getElementById('newsForm');

    form.addEventListener('submit', (e) => {
      e.preventDefault();

      const submitBtn = form.querySelector('button[type="submit"]');
      submitBtn.disabled = true;
      submitBtn.textContent = '投稿中...';

      const formData = new FormData(form);

      fetch('/attendance-v2/public/index.php/news/store', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(res => {
        if (res.status === 'success') {
          alert(res.message || 'お知らせを投稿しました');
          window.location.href = '/news';
        } else {
          alert(res.message || '投稿に失敗しました');
          submitBtn.disabled = false;
          submitBtn.textContent = '📤 投稿する';
        }
      })
      .catch(err => {
        console.error(err);
        alert('エラーが発生しました');
        submitBtn.disabled = false;
        submitBtn.textContent = '📤 投稿する';
      });
    });
  </script>
</body>
</html>
