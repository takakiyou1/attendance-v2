<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title) ?></title>
  <link rel="stylesheet" href="<?= asset('css/modern-design.css') ?>">
</head>
<body>
  <div style="min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: var(--space-lg);">
    <div class="card" style="text-align: center; max-width: 500px;">
      <h1 style="color: var(--color-primary); margin-bottom: var(--space-md);"><?= htmlspecialchars($title) ?></h1>
      <p style="font-size: var(--text-lg); color: var(--color-text-secondary); margin-bottom: var(--space-xl);">
        <?= htmlspecialchars($message) ?>
      </p>
      <a href="<?= url('hello') ?>" class="btn btn-primary btn-lg">
        Helloページへ →
      </a>
    </div>
  </div>
</body>
</html>
