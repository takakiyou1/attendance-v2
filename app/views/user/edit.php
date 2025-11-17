<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title><?= $mode === 'create' ? '新規ユーザー登録' : 'ユーザー編集' ?></title>
  <style>
    form { width: 400px; margin: 40px auto; background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 10px; }
    label { display: block; margin: 10px 0 5px; }
    input, select { width: 100%; padding: 8px; box-sizing: border-box; }
    button { margin-top: 20px; padding: 10px; width: 100%; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
    button:hover { background: #0056b3; }
    a { display: block; text-align: center; margin-top: 20px; color: #007bff; text-decoration: none; }
  </style>
</head>
<body>
  <h1 style="text-align:center;"><?= $mode === 'create' ? '新規ユーザー登録' : 'ユーザー編集' ?></h1>
  <form method="POST" action="/user/save">
    <?php if ($mode === 'edit'): ?>
      <input type="hidden" name="id" value="<?= htmlspecialchars($user['id']) ?>">
    <?php endif; ?>

    <label>名前</label>
    <input type="text" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>

    <label>メール</label>
    <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>

    <label>パスワード <?= $mode === 'edit' ? '(変更する場合のみ入力)' : '' ?></label>
    <input type="password" name="password">

    <label>権限</label>
    <select name="role">
      <option value="employee" <?= ($user['role'] ?? '') === 'employee' ? 'selected' : '' ?>>スタッフ</option>
      <option value="admin" <?= ($user['role'] ?? '') === 'admin' ? 'selected' : '' ?>>管理者</option>
    </select>

    <label>報酬タイプ</label>
    <select name="pay_type">
      <option value="hourly" <?= ($user['pay_type'] ?? '') === 'hourly' ? 'selected' : '' ?>>時給</option>
      <option value="fixed" <?= ($user['pay_type'] ?? '') === 'fixed' ? 'selected' : '' ?>>固定</option>
    </select>

    <label>報酬金額</label>
    <input type="number" name="pay_rate" value="<?= htmlspecialchars($user['pay_rate'] ?? 0) ?>" required>

    <button type="submit"><?= $mode === 'create' ? '登録' : '更新' ?></button>
  </form>

  <a href="/user/list">← 一覧に戻る</a>
</body>
</html>
