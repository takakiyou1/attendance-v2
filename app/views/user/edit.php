<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $mode === 'create' ? '新規ユーザー登録' : 'ユーザー編集' ?> | Re:time</title>
  <link rel="stylesheet" href="<?= asset('css/modern-design.css') ?>">
</head>
<body>
  <!-- Header -->
  <div class="page-header">
    <div class="container">
      <div class="page-header-content">
        <h1 class="page-title">
          <?= $mode === 'create' ? '➕ 新規ユーザー登録' : '✏️ ユーザー編集' ?>
        </h1>
        <div class="page-actions">
          <a href="<?= url('user/list') ?>" class="btn btn-secondary">
            ← 一覧に戻る
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="container">
    <div class="card" style="max-width: 500px; margin: 0 auto;">
      <form method="POST" action="<?= url('user/save') ?>">
        <?php if ($mode === 'edit'): ?>
          <input type="hidden" name="id" value="<?= htmlspecialchars($user['id']) ?>">
        <?php endif; ?>

        <div class="form-group">
          <label class="form-label">名前</label>
          <input type="text" name="name" class="form-input" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
        </div>

        <div class="form-group">
          <label class="form-label">メール</label>
          <input type="email" name="email" class="form-input" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
        </div>

        <div class="form-group">
          <label class="form-label">パスワード <?= $mode === 'edit' ? '(変更する場合のみ入力)' : '' ?></label>
          <input type="password" name="password" class="form-input">
        </div>

        <div class="form-group">
          <label class="form-label">権限</label>
          <select name="role" class="form-select">
            <option value="employee" <?= ($user['role'] ?? '') === 'employee' ? 'selected' : '' ?>>スタッフ</option>
            <option value="admin" <?= ($user['role'] ?? '') === 'admin' ? 'selected' : '' ?>>管理者</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">報酬タイプ</label>
          <select name="pay_type" class="form-select">
            <option value="hourly" <?= ($user['pay_type'] ?? '') === 'hourly' ? 'selected' : '' ?>>時給</option>
            <option value="fixed" <?= ($user['pay_type'] ?? '') === 'fixed' ? 'selected' : '' ?>>固定</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">報酬金額（円）</label>
          <input type="number" name="pay_rate" class="form-input" value="<?= htmlspecialchars($user['pay_rate'] ?? 0) ?>" required>
        </div>

        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
          <?= $mode === 'create' ? '登録' : '更新' ?>
        </button>
      </form>
    </div>
  </div>
</body>
</html>
