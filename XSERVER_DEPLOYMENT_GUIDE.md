# Re:time 勤怠管理システム - Xserver デプロイメントガイド

このガイドは、あなたの `attendance-v2` プロジェクトをXserverに正確にデプロイするための完全な手順書です。

---

## 📋 目次

1. [事前準備](#事前準備)
2. [フォルダ構造の理解](#フォルダ構造の理解)
3. [Xserverへのファイルアップロード](#xserverへのファイルアップロード)
4. [設定ファイルの修正](#設定ファイルの修正)
5. [データベースのセットアップ](#データベースのセットアップ)
6. [.htaccess の設定](#htaccess-の設定)
7. [最終チェックリスト](#最終チェックリスト)
8. [トラブルシューティング](#トラブルシューティング)

---

## 事前準備

### 必要な情報を準備

Xserverサーバーパネルから以下の情報を確認してください：

- **ドメイン名**: 例) `yourdomain.com`
- **FTPホスト**: 例) `sv12345.xserver.jp`
- **FTPユーザー名**: サーバーID
- **FTPパスワード**: 設定したパスワード
- **MySQL情報**:
  - データベース名: 例) `yourdomain_retime`
  - ユーザー名: 例) `yourdomain_user`
  - パスワード: 設定したパスワード
  - ホスト名: `mysql1234.xserver.jp` (通常は `localhost` でも可)

### ローカルで必要な作業

1. **データベースのエクスポート**
   - MAMPのphpMyAdminを開く
   - `attendance_v2` データベースを選択
   - 「エクスポート」タブ → 「実行」
   - `attendance_v2.sql` として保存

2. **不要ファイルの削除（オプション）**
   後述のアップロード前に、以下のファイル・フォルダは削除してもOK：
   - `test_*.php` (テストファイル)
   - `.git/` フォルダ
   - `.claude/` フォルダ
   - `REFACTORING_*.md` (開発用ドキュメント)
   - `old_*.php` (旧ファイル)

---

## フォルダ構造の理解

### Xserverのディレクトリ構造

```
あなたのXserverアカウント/
├── yourdomain.com/           ← ドメインフォルダ
│   └── public_html/          ← ドキュメントルート（Webから直接アクセス可能）
│       ├── index.php         ← ここにpublic/index.phpを配置
│       ├── css/              ← ここにpublic/css/を配置
│       └── .htaccess         ← URLリライト設定
│
└── retime/                   ← セキュリティのため public_html の外に配置
    ├── app/
    ├── config/
    ├── core/
    ├── api/
    ├── docs/
    └── migrations/
```

### 重要なポイント

🔒 **セキュリティ**: `app/`, `config/`, `core/` フォルダは `public_html` の外に配置することで、Webから直接アクセスされるのを防ぎます。

📁 **public_html に配置**: ユーザーがブラウザでアクセスする必要があるファイルのみ。

---

## Xserverへのファイルアップロード

### ステップ1: FTP接続

FileZillaまたは任意のFTPクライアントを使用：

1. **ホスト**: `sv12345.xserver.jp`
2. **ユーザー名**: サーバーID
3. **パスワード**: FTPパスワード
4. **ポート**: 21
5. **暗号化**: 明示的なFTP over TLSを使用

### ステップ2: フォルダ作成

FTPで接続後、以下のフォルダを作成：

```
/home/サーバーID/yourdomain.com/
                                └── public_html/    ← 既存
/home/サーバーID/retime/            ← 新規作成
```

`retime/` フォルダは `public_html` と **同じ階層** に作成してください。

### ステップ3: ファイルのアップロード

#### A. public_html へアップロード

**ローカル** → **Xserver**

| ローカルのファイル/フォルダ | アップロード先 |
|---------------------------|--------------|
| `public/index.php` | `public_html/index.php` |
| `public/css/` フォルダごと | `public_html/css/` |

#### B. retime/ へアップロード

**ローカル** → **Xserver**

| ローカルのファイル/フォルダ | アップロード先 |
|---------------------------|--------------|
| `app/` フォルダごと | `retime/app/` |
| `config/` フォルダごと | `retime/config/` |
| `core/` フォルダごと | `retime/core/` |
| `api/` フォルダごと | `retime/api/` |
| `docs/` フォルダごと (任意) | `retime/docs/` |
| `migrations/` フォルダごと (任意) | `retime/migrations/` |

#### アップロード後の構造

```
/home/サーバーID/
├── yourdomain.com/
│   └── public_html/
│       ├── index.php
│       ├── css/
│       │   └── modern-design.css
│       └── .htaccess (次のステップで作成)
│
└── retime/
    ├── app/
    │   ├── controllers/
    │   ├── models/
    │   └── views/
    ├── config/
    │   ├── database.php
    │   ├── database_new.php
    │   └── session.php
    ├── core/
    │   ├── Controller.php
    │   ├── Database.php
    │   ├── Model.php
    │   ├── Response.php
    │   ├── Router.php
    │   ├── Session.php
    │   └── View.php
    └── api/
        ├── get_all_shifts.php
        └── get_my_shifts.php
```

---

## 設定ファイルの修正

### 1. public_html/index.php の修正

**現在のコード (8-14行目):**
```php
// Load core classes
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/View.php';
```

**修正後 (Xserver本番環境用):**
```php
// Load core classes
require_once __DIR__ . '/../../retime/core/Database.php';
require_once __DIR__ . '/../../retime/core/Session.php';
require_once __DIR__ . '/../../retime/core/Response.php';
require_once __DIR__ . '/../../retime/core/Router.php';
require_once __DIR__ . '/../../retime/core/Controller.php';
require_once __DIR__ . '/../../retime/core/Model.php';
require_once __DIR__ . '/../../retime/core/View.php';
```

**現在のコード (17-18行目):**
```php
// Load configurations
$dbConfig = require __DIR__ . '/../config/database_new.php';
$sessionConfig = require __DIR__ . '/../config/session.php';
```

**修正後:**
```php
// Load configurations
$dbConfig = require __DIR__ . '/../../retime/config/database_new.php';
$sessionConfig = require __DIR__ . '/../../retime/config/session.php';
```

### 2. retime/config/database.php の修正

**ファイル**: `retime/config/database.php`

**現在のコード:**
```php
<?php
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=attendance_v2;charset=utf8',
        'root',
        'root' // ← MAMPのデフォルト。XAMPPなら空文字 ''
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'データベース接続エラー: ' . $e->getMessage();
    exit;
}
?>
```

**修正後 (Xserver本番環境用):**
```php
<?php
try {
    $pdo = new PDO(
        'mysql:host=mysql1234.xserver.jp;dbname=yourdomain_retime;charset=utf8',
        'yourdomain_user',
        'YOUR_DATABASE_PASSWORD'
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // 本番環境ではエラーメッセージを隠す
    error_log('Database connection error: ' . $e->getMessage());
    echo 'システムエラーが発生しました。管理者にお問い合わせください。';
    exit;
}
?>
```

⚠️ **置き換え箇所**:
- `mysql1234.xserver.jp` → あなたのMySQLホスト
- `yourdomain_retime` → 作成したデータベース名
- `yourdomain_user` → データベースユーザー名
- `YOUR_DATABASE_PASSWORD` → データベースパスワード

### 3. retime/config/database_new.php の修正

**ファイル**: `retime/config/database_new.php`

**修正後:**
```php
<?php
/**
 * Database Configuration (Production)
 */
return [
    'host' => 'mysql1234.xserver.jp',  // ← Xserver MySQLホスト
    'database' => 'yourdomain_retime', // ← データベース名
    'username' => 'yourdomain_user',   // ← ユーザー名
    'password' => 'YOUR_DATABASE_PASSWORD', // ← パスワード
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
];
```

### 4. core/Controller.php のビューパス修正

**ファイル**: `retime/core/Controller.php`

現在のコードを確認し、`view()` メソッドのパスが正しいか確認：

```php
protected function view(string $viewPath, array $data = []): void
{
    // $viewPath は 'shift/calendar' のような形式
    $viewFile = __DIR__ . '/../app/views/' . $viewPath . '.php';

    if (!file_exists($viewFile)) {
        Response::error('View not found: ' . $viewPath);
    }

    extract($data);
    require $viewFile;
}
```

このコードは修正不要です（相対パスのため）。

### 5. ビューファイル内のアセットパス修正

**重要**: すべてのビューファイル（`.php`）内のCSSやJSのパスを絶対パスに変更する必要があります。

#### 修正が必要なファイル例:

**app/views/menu/index.php**

**現在のコード (7行目):**
```html
<link rel="stylesheet" href="/attendance-v2/public/css/modern-design.css">
```

**修正後:**
```html
<link rel="stylesheet" href="/css/modern-design.css">
```

#### 一括置換が必要な箇所:

すべてのビューファイル (`app/views/**/*.php`) で以下を検索・置換：

| 検索 | 置換 |
|------|------|
| `/attendance-v2/public/css/` | `/css/` |
| `/attendance-v2/public/index.php/` | `/` |
| `href="/attendance-v2/public/index.php/` | `href="/` |
| `action="/attendance-v2/public/index.php/` | `action="/` |

**影響を受けるファイル**:
- `app/views/menu/index.php`
- `app/views/shift/calendar.php`
- `app/views/shift/view_all.php`
- `app/views/shift/view_my.php`
- `app/views/shift/my_day.php`
- `app/views/shift/day_gantt.php`
- `app/views/news/index.php`
- `app/views/news/create.php`
- その他すべてのビューファイル

---

## データベースのセットアップ

### ステップ1: データベースの作成

1. **Xserverサーバーパネルにログイン**
2. **「MySQL設定」をクリック**
3. **「MySQLデータベースの追加」タブ**
   - データベース名: `yourdomain_retime` (例)
   - 文字コード: `UTF-8`
   - 「確認画面へ進む」→ 「追加する」

4. **「MySQLユーザの追加」タブ**
   - ユーザーID: `yourdomain_user` (例)
   - パスワード: 強力なパスワードを設定
   - 「確認画面へ進む」→ 「追加する」

5. **「アクセス権所有ユーザ」タブ**
   - 先ほど作成したデータベースを選択
   - 先ほど作成したユーザーを選択
   - 「追加」をクリック

### ステップ2: データベースのインポート

1. **phpMyAdmin にアクセス**
   - サーバーパネル → 「phpMyAdmin」をクリック
   - ユーザー名とパスワードでログイン

2. **データベースを選択**
   - 左側のメニューから `yourdomain_retime` を選択

3. **SQLファイルのインポート**
   - 上部タブ「インポート」をクリック
   - 「ファイルを選択」→ ローカルの `attendance_v2.sql` を選択
   - 「実行」をクリック

4. **インポート成功の確認**
   - 「インポートは正常に終了しました」と表示されることを確認
   - テーブルが正しく作成されているか確認（`users`, `shifts`, `pay_rate_history` など）

### ステップ3: テーブル構造の確認

以下のテーブルが存在することを確認：

- `users` - ユーザー情報
- `shifts` - シフトデータ
- `pay_rate_history` - 報酬設定履歴
- `manual_payments` - 手動報酬調整
- `special_allowances` - 特別手当
- `news` - お知らせ

---

## .htaccess の設定

### public_html/.htaccess を作成

FTPクライアントまたはXserverのファイルマネージャで、`public_html/` に `.htaccess` ファイルを作成：

**ファイル名**: `public_html/.htaccess`

**内容**:
```apache
# Re:time 勤怠管理システム
# URL Rewriting for Clean URLs

<IfModule mod_rewrite.c>
    RewriteEngine On

    # HTTPS強制（SSL証明書を設定している場合）
    # RewriteCond %{HTTPS} off
    # RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L]

    # リクエストが実ファイル・ディレクトリでない場合、index.phpへリダイレクト
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>

# セキュリティ設定
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>

# ディレクトリリスティング無効化
Options -Indexes

# エラーページのカスタマイズ（任意）
# ErrorDocument 404 /error/404.php
# ErrorDocument 500 /error/500.php
```

### .htaccess の動作確認

アップロード後、ブラウザで以下にアクセス：

- `http://yourdomain.com/` → トップページが表示されるはず
- `http://yourdomain.com/login` → ログインページが表示されるはず
- `http://yourdomain.com/menu` → ログイン後メニューが表示されるはず

---

## 最終チェックリスト

デプロイ完了後、以下を順番にチェックしてください：

### ✅ ファイル・フォルダの確認

- [ ] `public_html/index.php` が存在する
- [ ] `public_html/css/modern-design.css` が存在する
- [ ] `public_html/.htaccess` が存在する
- [ ] `retime/app/` フォルダが存在する
- [ ] `retime/config/` フォルダが存在する
- [ ] `retime/core/` フォルダが存在する

### ✅ 設定ファイルの確認

- [ ] `public_html/index.php` のパスが `../../retime/` に修正されている
- [ ] `retime/config/database.php` のDB情報が本番環境用に更新されている
- [ ] `retime/config/database_new.php` のDB情報が本番環境用に更新されている

### ✅ ビューファイルのパス確認

- [ ] すべてのビューファイルで `/attendance-v2/public/` が削除されている
- [ ] CSS パスが `/css/modern-design.css` になっている
- [ ] フォームの action が `/login`, `/shift/save` など正しいパスになっている

### ✅ 動作確認

#### 1. ログイン機能
- [ ] `http://yourdomain.com/login` にアクセス
- [ ] テストユーザーでログインできる
- [ ] ログイン後、メニューページに遷移する

#### 2. メニュー表示
- [ ] ログイン後、`http://yourdomain.com/menu` が表示される
- [ ] ヘッダーに「⏱️ Re:time」ロゴが表示される
- [ ] フッターに「© 2025 Re:time」が表示される
- [ ] CSSが正しく読み込まれている（デザインが崩れていない）

#### 3. 管理者機能（adminでログイン）
- [ ] ユーザー一覧 (`/user/list`) が表示される
- [ ] シフト管理カレンダー (`/shift/calendar`) が表示される
- [ ] FullCalendar が正しく動作する
- [ ] シフトの作成・編集・削除ができる
- [ ] 報酬一覧 (`/pay/summary`) が表示される
- [ ] お知らせ (`/news`) が表示される

#### 4. スタッフ機能（staffでログイン）
- [ ] 全体シフト (`/shift/view_all`) が表示される
- [ ] 自分のシフト (`/shift/view_my`) が表示される
- [ ] 日付をクリックすると詳細ページ (`/shift/my_day`) が表示される
- [ ] 自分の報酬 (`/pay/my_pay`) が表示される
- [ ] お知らせ (`/news`) が表示される

#### 5. 深夜シフト表示
- [ ] 22:00-03:00 のような深夜シフトが月間ビューで1つのブロックとして表示される
- [ ] 週間ビューで深夜シフトが27:00のような拡張時間で表示される
- [ ] 報酬計算で深夜シフトが正しく計算される（負の値にならない）

#### 6. API エンドポイント
- [ ] `/api/shifts/all` が JSON を返す
- [ ] `/api/shifts/my` が JSON を返す
- [ ] FullCalendar がこれらのAPIからデータを取得できる

### ✅ セキュリティ確認

- [ ] `http://yourdomain.com/config/database.php` に直接アクセスできない（404エラー）
- [ ] `http://yourdomain.com/app/` に直接アクセスできない（404エラー）
- [ ] `http://yourdomain.com/core/` に直接アクセスできない（404エラー）
- [ ] データベースパスワードが強力である
- [ ] 本番環境でデバッグ情報が表示されない

### ✅ パフォーマンス確認

- [ ] ページの読み込み速度が許容範囲内
- [ ] FullCalendar の動作がスムーズ
- [ ] データベースクエリが適切に実行されている

---

## トラブルシューティング

### 問題1: 「500 Internal Server Error」が表示される

**原因**:
- `.htaccess` の構文エラー
- パスの設定ミス
- PHPバージョンの不一致

**解決方法**:
1. `.htaccess` を一時的にリネームして動作確認
2. Xserverのエラーログを確認（サーバーパネル → エラーログ）
3. `public_html/index.php` のパスが正しいか確認
4. PHPバージョンを確認（Xserverでは PHP 7.4 以上を推奨）

### 問題2: 「データベース接続エラー」が表示される

**原因**:
- データベース情報の設定ミス
- データベースユーザーのアクセス権限不足

**解決方法**:
1. `retime/config/database.php` の情報が正しいか再確認
2. phpMyAdmin で該当データベースにアクセスできるか確認
3. ユーザーのアクセス権限を再設定

### 問題3: CSSが読み込まれない（デザインが崩れる）

**原因**:
- CSSファイルのパスが間違っている
- CSSファイルがアップロードされていない

**解決方法**:
1. ブラウザの開発者ツール（F12）でネットワークタブを確認
2. CSS ファイルのパスが `/css/modern-design.css` になっているか確認
3. `public_html/css/modern-design.css` が存在するか確認
4. ビューファイルのパスを修正

### 問題4: ログインできない

**原因**:
- セッションが開始されていない
- データベースにユーザーデータがない

**解決方法**:
1. phpMyAdmin で `users` テーブルにデータがあるか確認
2. `retime/config/session.php` が正しく読み込まれているか確認
3. Xserver の session ディレクトリの書き込み権限を確認

### 問題5: FullCalendar が表示されない

**原因**:
- JavaScript のパスエラー
- API エンドポイントのパスが間違っている

**解決方法**:
1. ブラウザのコンソール（F12）でJavaScriptエラーを確認
2. ビューファイルの API パスを確認:
   ```javascript
   // 正: events: '/api/shifts/all'
   // 誤: events: '/attendance-v2/public/index.php/api/shifts/all'
   ```
3. `/api/shifts/all` に直接アクセスしてJSONが返ってくるか確認

### 問題6: 深夜シフトが2日に分かれて表示される

**原因**:
- `nextDayThreshold` の設定が反映されていない
- ビューファイルが古いままになっている

**解決方法**:
1. ブラウザのキャッシュをクリア（Ctrl+Shift+Delete）
2. ビューファイル（`calendar.php`, `view_all.php`, `view_my.php`）に以下が含まれているか確認:
   ```javascript
   slotMinTime: '09:00:00',
   slotMaxTime: '29:00:00',
   nextDayThreshold: '09:00:00',
   ```

### 問題7: 画像やアイコンが表示されない

**原因**:
- 絵文字が使用されているが、フォントが対応していない
- 画像ファイルがアップロードされていない

**解決方法**:
1. 絵文字（📅、⏱️など）は UTF-8 で保存されているか確認
2. 画像を使用している場合は `public_html/images/` フォルダを作成してアップロード
3. ブラウザが絵文字をサポートしているか確認（最新版推奨）

---

## 本番環境の最適化（任意）

### PHP設定の最適化

Xserver サーバーパネル → 「php.ini設定」で以下を調整：

```ini
memory_limit = 256M
max_execution_time = 60
upload_max_filesize = 20M
post_max_size = 20M
```

### SSL証明書の設定

1. Xserver サーバーパネル → 「SSL設定」
2. 無料独自SSL を設定
3. `.htaccess` の HTTPS 強制リダイレクトを有効化（コメント解除）

### エラー表示の無効化

本番環境では、PHP エラーを画面に表示しないようにする：

**retime/config/database.php** の catch ブロック:
```php
catch (PDOException $e) {
    // 本番環境ではエラーメッセージを隠す
    error_log('Database connection error: ' . $e->getMessage());
    echo 'システムエラーが発生しました。管理者にお問い合わせください。';
    exit;
}
```

### バックアップの設定

1. Xserver の自動バックアップ機能を確認
2. 定期的にデータベースをエクスポート（サーバーパネル → データベースバックアップ）
3. FTP経由で定期的にファイルをバックアップ

---

## まとめ

このガイドに従って、Re:time 勤怠管理システムを Xserver に正しくデプロイできます。

### デプロイの流れ（概要）

1. **ファイル準備**: ローカルでDBエクスポート、不要ファイル削除
2. **FTPアップロード**: `public_html/` と `retime/` に分けてアップロード
3. **設定修正**: `index.php` と `database.php` のパスを本番環境用に修正
4. **DBセットアップ**: Xserver で DB 作成、SQLインポート
5. **.htaccess 作成**: URLリライト設定
6. **動作確認**: チェックリストに従って全機能をテスト

### 重要なポイント

✅ **セキュリティ**: `app/`, `config/`, `core/` は必ず `public_html` の外に配置
✅ **パス修正**: すべてのビューファイルで `/attendance-v2/public/` を削除
✅ **DB設定**: 本番環境用の DB 情報に必ず変更
✅ **テスト**: デプロイ後、必ず全機能をテストする

---

## サポート

問題が解決しない場合:

1. **Xserver エラーログを確認**: サーバーパネル → エラーログ
2. **ブラウザ開発者ツールを確認**: F12 → Console タブ・Network タブ
3. **Xserver サポートに問い合わせ**: 技術的な問題の場合

---

**デプロイ成功をお祈りしています！🚀**

**Re:time 勤怠管理システム**
**Version**: 2.0
**Last Updated**: 2025-11-17
