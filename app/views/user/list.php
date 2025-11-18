<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ユーザー一覧 | Re:time</title>
  <link rel="stylesheet" href="<?= asset('css/modern-design.css') ?>">
</head>
<body>
  <!-- Header -->
  <div class="page-header">
    <div class="container">
      <div class="page-header-content">
        <h1 class="page-title">
          👥 ユーザー一覧
        </h1>
        <div class="page-actions">
          <a href="<?= url('user/create') ?>" class="btn btn-primary">
            ＋ 新規ユーザー追加
          </a>
          <a href="<?= url('menu') ?>" class="btn btn-secondary">
            ← メニューに戻る
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="container">
    <div class="table-wrapper">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>名前</th>
            <th>メール</th>
            <th>権限</th>
            <th>報酬タイプ</th>
            <th>金額</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td><?= htmlspecialchars($u['id']) ?></td>
              <td><?= htmlspecialchars($u['name']) ?></td>
              <td><?= htmlspecialchars($u['email']) ?></td>
              <td>
                <span class="badge <?= $u['role'] === 'admin' ? 'badge-primary' : 'badge-success' ?>">
                  <?= $u['role'] === 'admin' ? '管理者' : 'スタッフ' ?>
                </span>
              </td>
              <td><?= $u['pay_type'] === 'fixed' ? '固定' : '時間報酬' ?></td>
              <td><?= number_format($u['pay_rate']) ?> 円</td>
              <td>
                <a href="<?= url('user/edit') ?>?id=<?= $u['id'] ?>" class="btn btn-sm btn-secondary">編集</a>
                <a href="<?= url('user/delete') ?>?id=<?= $u['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('削除しますか？')">削除</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
