<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>報酬編集</title>
<style>
body { font-family: Meiryo, sans-serif; margin: 40px; text-align:center; }
form { display:inline-block; text-align:left; }
label { display:block; margin-top:10px; }
input { width:100%; padding:8px; }
button { margin-top:15px; padding:10px 20px; background:#007bff; color:white; border:none; border-radius:5px; cursor:pointer; }
.back-btn {
  background:#6c757d;
  color:white;
  margin-left:10px;
}
</style>
</head>
<body>
  <h1>報酬編集（<?= htmlspecialchars($month) ?>）</h1>
  <form id="payEditForm">
    <input type="hidden" name="user_id" value="<?= $user_id ?>">
    <input type="hidden" name="month" value="<?= $month ?>">

    <p>スタッフ名：<?= htmlspecialchars($user['name']) ?></p>
    <p>報酬タイプ：<?= htmlspecialchars($user['pay_type']) ?></p>
    <p>自動計算額：<?= number_format(($user['pay_type']==='fixed') ? $user['pay_rate'] : $user['auto_amount']) ?> 円</p>

    <label>上書き金額</label>
    <input type="number" name="new_amount" value="<?= htmlspecialchars($user['manual_amount'] ?? '') ?>">

    <div style="margin-top:20px;">
      <button type="submit">保存</button>
      <!-- ✅ 戻るボタンを追加 -->
      <button type="button" class="back-btn" onclick="history.back();">戻る</button>
    </div>
  </form>

  <script>
  document.getElementById('payEditForm').addEventListener('submit', e => {
    e.preventDefault();
    fetch('/attendance-v2/public/index.php/attendance-v2/api/save_pay_edit.php', {
      method: 'POST',
      body: new FormData(e.target)
    })
    .then(res => res.json())
    .then(res => {
      alert(res.message);
      location.href = '/pay/summary';
    });
  });
  </script>
</body>
</html>
