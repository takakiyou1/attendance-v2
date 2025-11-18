<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ログイン | Re:time</title>
  <link rel="stylesheet" href="<?= asset('css/modern-design.css') ?>">
  <style>
    .login-container {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: var(--space-lg);
    }
    .login-logo {
      font-size: var(--text-4xl);
      font-weight: var(--font-bold);
      color: var(--color-primary);
      margin-bottom: var(--space-sm);
      letter-spacing: 2px;
    }
    .login-tagline {
      color: var(--color-text-secondary);
      margin-bottom: var(--space-2xl);
      font-size: var(--text-sm);
    }
    .login-card {
      background: var(--color-bg-primary);
      padding: var(--space-2xl);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-md);
      width: 100%;
      max-width: 400px;
    }
    .login-card .form-group {
      margin-bottom: var(--space-md);
    }
    .login-card .form-input {
      width: 100%;
    }
    .login-card .btn {
      width: 100%;
      margin-top: var(--space-md);
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="login-logo">Re:time</div>
    <p class="login-tagline">シフト・勤怠管理システム</p>

    <?php if (!empty($error)): ?>
      <div class="alert alert-error" style="max-width: 400px; width: 100%; margin-bottom: var(--space-md);">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <div class="login-card">
      <form method="POST" action="<?= url('login') ?>">
        <div class="form-group">
          <label class="form-label" for="email">メールアドレス</label>
          <input type="email" id="email" name="email" class="form-input" placeholder="example@email.com" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="password">パスワード</label>
          <input type="password" id="password" name="password" class="form-input" placeholder="パスワード" required>
        </div>
        <button type="submit" class="btn btn-primary btn-lg">ログイン</button>
      </form>
    </div>
  </div>
</body>
</html>
