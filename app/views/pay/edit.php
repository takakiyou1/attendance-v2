<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>報酬編集 | Re:time</title>
  <link rel="stylesheet" href="<?= asset('css/modern-design.css') ?>">
</head>
<body>
  <!-- Header -->
  <div class="page-header">
    <div class="container">
      <div class="page-header-content">
        <h1 class="page-title">
          ✏️ 報酬編集（<?= htmlspecialchars($month) ?>）
        </h1>
        <div class="page-actions">
          <button type="button" class="btn btn-secondary" onclick="history.back();">
            ← 戻る
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="container">
    <div class="card" style="max-width: 500px; margin: 0 auto;">
      <form id="payEditForm">
        <input type="hidden" name="user_id" value="<?= $user_id ?>">
        <input type="hidden" name="month" value="<?= $month ?>">

        <div class="form-group">
          <label class="form-label">スタッフ名</label>
          <p style="font-weight: var(--font-semibold); margin: 0;"><?= htmlspecialchars($user['name']) ?></p>
        </div>

        <div class="form-group">
          <label class="form-label">報酬タイプ</label>
          <p style="margin: 0;">
            <span class="badge <?= $user['pay_type'] === 'fixed' ? 'badge-primary' : 'badge-success' ?>">
              <?= $user['pay_type'] === 'fixed' ? '固定' : '時給' ?>
            </span>
          </p>
        </div>

        <div class="form-group">
          <label class="form-label">自動計算額</label>
          <p style="font-size: var(--text-xl); font-weight: var(--font-bold); color: var(--color-primary); margin: 0;">
            <?= number_format(($user['pay_type']==='fixed') ? $user['pay_rate'] : $user['auto_amount']) ?> 円
          </p>
        </div>

        <div class="form-group">
          <label class="form-label">上書き金額（円）</label>
          <input type="number" name="new_amount" class="form-input" value="<?= htmlspecialchars($user['manual_amount'] ?? '') ?>" placeholder="空欄の場合は自動計算額を使用">
        </div>

        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">保存</button>
      </form>
    </div>
  </div>

  <script>
  document.getElementById('payEditForm').addEventListener('submit', e => {
    e.preventDefault();
    fetch('<?= url('api/save_pay_edit') ?>', {
      method: 'POST',
      body: new FormData(e.target)
    })
    .then(res => res.json())
    .then(res => {
      alert(res.message);
      location.href = '<?= url('pay/summary') ?>';
    });
  });
  </script>
</body>
</html>
