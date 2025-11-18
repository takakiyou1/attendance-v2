<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>お知らせ | Re:time</title>
  <link rel="stylesheet" href="<?= asset('css/modern-design.css') ?>">
</head>
<body>
  <!-- Header -->
  <div class="page-header">
    <div class="container">
      <div class="page-header-content">
        <h1 class="page-title">
          📢 お知らせ
        </h1>
        <div class="page-actions">
          <?php if (in_array($_SESSION['user']['role'], ['admin', 'poster'])): ?>
            <a href="<?= url('news/create') ?>" class="btn btn-primary">
              ➕ 新規投稿
            </a>
          <?php endif; ?>
          <a href="<?= url('menu') ?>" class="btn btn-secondary">
            ← メニューに戻る
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="container">
    <?php if (empty($news)): ?>
      <div class="empty-state">
        <div class="empty-state-icon">📭</div>
        <h3 class="empty-state-title">お知らせはまだありません</h3>
        <p class="empty-state-desc">新しいお知らせが投稿されるとここに表示されます</p>
      </div>
    <?php else: ?>
      <?php foreach ($news as $item): ?>
        <div class="card">
          <h2 style="margin-bottom: var(--space-sm); color: var(--color-text-primary);">
            <?= htmlspecialchars($item['title']) ?>
          </h2>

          <div style="display: flex; align-items: center; gap: var(--space-md); margin-bottom: var(--space-md); flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: var(--space-sm);">
              <span style="font-size: var(--text-sm); color: var(--color-text-tertiary);">投稿者:</span>
              <span class="badge badge-primary"><?= htmlspecialchars($item['author_name'] ?? '不明') ?></span>
            </div>
            <div style="display: flex; align-items: center; gap: var(--space-sm);">
              <span style="font-size: var(--text-sm); color: var(--color-text-tertiary);">投稿日時:</span>
              <span style="font-size: var(--text-sm); color: var(--color-text-secondary);">
                <?= htmlspecialchars($item['created_at']) ?>
              </span>
            </div>
          </div>

          <div style="white-space: pre-wrap; line-height: var(--leading-relaxed); color: var(--color-text-primary); padding: var(--space-md); background: var(--color-bg-secondary); border-radius: var(--radius-md); margin-bottom: var(--space-md);">
<?= htmlspecialchars($item['content']) ?></div>

          <?php if ($_SESSION['user']['role'] === 'admin'): ?>
            <div style="padding-top: var(--space-md); border-top: 1px solid var(--color-border-light);">
              <button class="btn btn-danger btn-sm" onclick="deleteNews(<?= $item['id'] ?>)">
                🗑️ 削除
              </button>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <script>
    function deleteNews(id) {
      if (!confirm('このお知らせを削除しますか？')) return;

      fetch('<?= url('news/delete') ?>', {
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
