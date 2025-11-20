<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>報酬設定編集 | Re:time</title>
  <link rel="stylesheet" href="<?= asset('css/modern-design.css') ?>">
</head>
<body>
  <!-- Header -->
  <div class="page-header">
    <div class="container">
      <div class="page-header-content">
        <h1 class="page-title">
          ⚙️ 報酬設定編集
        </h1>
        <div class="page-actions">
          <button type="button" class="btn btn-secondary" onclick="history.back()">
            ← 戻る
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="container">
    <div class="card" style="max-width: 500px; margin: 0 auto;">
      <form id="settingForm">
        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">

        <div class="form-group">
          <label class="form-label">スタッフ名</label>
          <p style="font-weight: var(--font-semibold); margin: 0;"><?= htmlspecialchars($user['name']) ?></p>
        </div>

        <div class="form-group">
          <label class="form-label">支払い方式</label>
          <select name="pay_type" class="form-select">
            <option value="hourly" <?= $user['pay_type'] === 'hourly' ? 'selected' : '' ?>>時間報酬</option>
            <option value="fixed" <?= $user['pay_type'] === 'fixed' ? 'selected' : '' ?>>固定</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">報酬金額（円）</label>
          <input type="number" name="pay_rate" class="form-input" value="<?= htmlspecialchars($user['pay_rate']) ?>" required>
        </div>

        <div class="form-group">
          <label class="form-label">報酬適用開始日</label>
          <input type="date" name="start_date" class="form-input" required value="<?= date('Y-m-d') ?>">
        </div>

        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">保存</button>
      </form>
    </div>
  </div>

  <script>
  document.getElementById('settingForm').addEventListener('submit', e => {
    e.preventDefault();
    fetch('<?= url('pay/save_pay_setting') ?>', {
      method: 'POST',
      body: new FormData(e.target)
    })
    .then(res => {
      console.log('Response status:', res.status);
      console.log('Response headers:', res.headers.get('content-type'));
      return res.text().then(text => {
        console.log('Response text:', text);
        try {
          return JSON.parse(text);
        } catch (e) {
          console.error('JSON parse error:', e);
          throw new Error('サーバーレスポンスが不正です: ' + text.substring(0, 100));
        }
      });
    })
    .then(res => {
      alert(res.message);
      if (res.status === 'success') {
        location.href = '<?= url('pay/settings') ?>';
      }
    })
    .catch(err => {
      console.error('Error details:', err);
      alert('エラーが発生しました: ' + err.message);
    });
  });
  </script>
</body>
</html>
