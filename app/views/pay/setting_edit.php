<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>報酬設定編集 | Re:time</title>
<style>
body { font-family: Meiryo, sans-serif; text-align:center; margin:40px; }
form { display:inline-block; text-align:left; }
label { display:block; margin-top:10px; }
select, input { width:100%; padding:8px; }
button { margin-top:15px; padding:10px 20px; border:none; border-radius:5px; color:white; cursor:pointer; }
.save { background:#007bff; }
.back { background:#6c757d; margin-left:10px; }
</style>
</head>
<body>
  <h1>報酬設定編集</h1>
  <form id="settingForm">
    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">

    <p>スタッフ名：<?= htmlspecialchars($user['name']) ?></p>

    <label>支払い方式</label>
    <select name="pay_type">
      <option value="hourly" <?= $user['pay_type'] === 'hourly' ? 'selected' : '' ?>>時給</option>
      <option value="fixed" <?= $user['pay_type'] === 'fixed' ? 'selected' : '' ?>>固定</option>
    </select>

    <label>報酬金額（円）</label>
    <input type="number" name="pay_rate" value="<?= htmlspecialchars($user['pay_rate']) ?>" required>
    
    <label>報酬適用開始日</label>
    <input type="date" name="start_date" required value="<?= date('Y-m-d') ?>">

    <div style="margin-top:20px;">
      <button type="submit" class="save">保存</button>
      <button type="button" class="back" onclick="history.back()">戻る</button>
    </div>
  </form>

  <script>
  document.getElementById('settingForm').addEventListener('submit', e => {
    e.preventDefault();
    fetch('/attendance-v2/api/save_pay_setting.php', {
      method: 'POST',
      body: new FormData(e.target)
    })
    .then(res => res.json())
    .then(res => {
      alert(res.message);
      location.href = '/pay/settings';
    });
  });
  </script>
</body>
</html>
