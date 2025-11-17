<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>メニュー | Re:time</title>
  <link rel="stylesheet" href="/css/modern-design.css">
  <style>
    body {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    .header {
      background: var(--color-bg-primary);
      border-bottom: 1px solid var(--color-border-light);
      padding: var(--space-lg) 0;
      box-shadow: var(--shadow-sm);
    }

    .header-content {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 var(--space-lg);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .logo {
      font-size: var(--text-2xl);
      font-weight: var(--font-bold);
      color: var(--color-text-primary);
    }

    .user-info {
      display: flex;
      align-items: center;
      gap: var(--space-md);
    }

    .user-avatar {
      width: 40px;
      height: 40px;
      border-radius: var(--radius-full);
      background: var(--color-primary-light);
      color: var(--color-primary);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: var(--font-semibold);
      font-size: var(--text-lg);
    }

    .user-details {
      text-align: right;
    }

    .user-name {
      font-weight: var(--font-semibold);
      color: var(--color-text-primary);
      margin: 0;
    }

    .user-role {
      font-size: var(--text-sm);
      color: var(--color-text-secondary);
      margin: 0;
    }

    .main-content {
      flex: 1;
      max-width: 1200px;
      width: 100%;
      margin: 0 auto;
      padding: var(--space-2xl) var(--space-lg);
    }

    .welcome {
      text-align: center;
      margin-bottom: var(--space-3xl);
    }

    .welcome h1 {
      font-size: var(--text-4xl);
      margin-bottom: var(--space-sm);
    }

    .welcome p {
      font-size: var(--text-lg);
      color: var(--color-text-secondary);
    }

    .menu-section {
      margin-bottom: var(--space-2xl);
    }

    .menu-section-title {
      font-size: var(--text-xl);
      font-weight: var(--font-semibold);
      color: var(--color-text-secondary);
      margin-bottom: var(--space-md);
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .footer {
      background: var(--color-bg-primary);
      border-top: 1px solid var(--color-border-light);
      padding: var(--space-lg);
      text-align: center;
    }

    .logout-btn {
      margin-top: var(--space-2xl);
      text-align: center;
    }

    @media (max-width: 768px) {
      .header-content {
        flex-direction: column;
        gap: var(--space-md);
      }

      .user-info {
        width: 100%;
        justify-content: space-between;
      }
    }
  </style>
</head>
<body>
  <!-- Header -->
  <div class="header">
    <div class="header-content">
      <div class="logo">⏱️ Re:time</div>
      <div class="user-info">
        <div class="user-avatar">
          <?= strtoupper(mb_substr($_SESSION['user']['name'], 0, 1)) ?>
        </div>
        <div class="user-details">
          <p class="user-name"><?= htmlspecialchars($_SESSION['user']['name']) ?></p>
          <p class="user-role">
            <span class="badge <?= $_SESSION['user']['role'] === 'admin' ? 'badge-primary' : 'badge-success' ?>">
              <?= htmlspecialchars($_SESSION['user']['role']) === 'admin' ? '管理者' : 'スタッフ' ?>
            </span>
          </p>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <div class="welcome">
      <h1>ようこそ、<?= htmlspecialchars($_SESSION['user']['name']) ?> さん</h1>
      <p>下記のメニューから操作を選択してください</p>
    </div>

    <?php if ($_SESSION['user']['role'] === 'admin'): ?>
      <!-- Admin Menu -->
      <div class="menu-section">
        <h2 class="menu-section-title">管理機能</h2>
        <div class="menu-grid">
          <a href="/user/list" class="menu-item">
            <div class="menu-item-icon">👥</div>
            <div class="menu-item-content">
              <div class="menu-item-title">ユーザー管理</div>
              <p class="menu-item-desc">スタッフの登録・編集・削除</p>
            </div>
          </a>

          <a href="/shift/calendar" class="menu-item">
            <div class="menu-item-icon">📅</div>
            <div class="menu-item-content">
              <div class="menu-item-title">シフト管理</div>
              <p class="menu-item-desc">シフトの作成・編集・削除</p>
            </div>
          </a>

          <a href="/pay/summary" class="menu-item">
            <div class="menu-item-icon">💰</div>
            <div class="menu-item-content">
              <div class="menu-item-title">報酬一覧</div>
              <p class="menu-item-desc">全スタッフの報酬確認</p>
            </div>
          </a>

          <a href="/pay/settings" class="menu-item">
            <div class="menu-item-icon">🧾</div>
            <div class="menu-item-content">
              <div class="menu-item-title">報酬設定</div>
              <p class="menu-item-desc">時給設定・報酬ルール管理</p>
            </div>
          </a>

          <a href="/news" class="menu-item">
            <div class="menu-item-icon">📢</div>
            <div class="menu-item-content">
              <div class="menu-item-title">お知らせ</div>
              <p class="menu-item-desc">社内通知の閲覧・投稿</p>
            </div>
          </a>
        </div>
      </div>

    <?php else: ?>
      <!-- Employee Menu -->
      <div class="menu-section">
        <h2 class="menu-section-title">スタッフ機能</h2>
        <div class="menu-grid">
          <a href="/shift/view_all" class="menu-item">
            <div class="menu-item-icon">📋</div>
            <div class="menu-item-content">
              <div class="menu-item-title">全体シフト</div>
              <p class="menu-item-desc">全員のシフトを確認</p>
            </div>
          </a>

          <a href="/shift/view_my" class="menu-item">
            <div class="menu-item-icon">📅</div>
            <div class="menu-item-content">
              <div class="menu-item-title">自分のシフト</div>
              <p class="menu-item-desc">あなたのシフトを確認</p>
            </div>
          </a>

          <a href="/news" class="menu-item">
            <div class="menu-item-icon">📢</div>
            <div class="menu-item-content">
              <div class="menu-item-title">お知らせ</div>
              <p class="menu-item-desc">社内通知を確認</p>
            </div>
          </a>

          <a href="/pay/my_pay" class="menu-item">
            <div class="menu-item-icon">💰</div>
            <div class="menu-item-content">
              <div class="menu-item-title">自分の報酬</div>
              <p class="menu-item-desc">給与・報酬を確認</p>
            </div>
          </a>
        </div>
      </div>
    <?php endif; ?>

    <!-- Logout -->
    <div class="logout-btn">
      <a href="/logout" class="btn btn-outline btn-lg">
        🚪 ログアウト
      </a>
    </div>
  </div>

  <!-- Footer -->
  <div class="footer">
    <p style="margin: 0; color: var(--color-text-tertiary); font-size: var(--text-sm);">
      © <?= date('Y') ?> Re:time
    </p>
  </div>
</body>
</html>
