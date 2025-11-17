# Re:time デプロイメントチェックリスト

このチェックリストを使用して、デプロイ作業を確実に進めてください。

---

## 📋 事前準備

- [ ] Xserver のFTP情報を確認済み
- [ ] Xserver のMySQL情報を確認済み
- [ ] ローカルの `attendance_v2` DBをエクスポート完了
- [ ] FTPクライアント（FileZilla等）をインストール済み

---

## 📤 ファイルアップロード

### public_html へ
- [ ] `public/index.php` → `public_html/index.php`
- [ ] `public/css/` → `public_html/css/`

### retime/ フォルダへ（public_html と同じ階層）
- [ ] `app/` フォルダ全体
- [ ] `config/` フォルダ全体
- [ ] `core/` フォルダ全体
- [ ] `api/` フォルダ全体（任意）
- [ ] `docs/` フォルダ全体（任意）

---

## ⚙️ 設定ファイル修正

### public_html/index.php
- [ ] 8-14行目: `__DIR__ . '/../core/` → `__DIR__ . '/../../retime/core/`
- [ ] 17-18行目: `__DIR__ . '/../config/` → `__DIR__ . '/../../retime/config/`

### retime/config/database.php
- [ ] `host` を Xserver の MySQL ホストに変更
- [ ] `dbname` を作成したDB名に変更
- [ ] `username` をDBユーザー名に変更
- [ ] `password` をDBパスワードに変更

### retime/config/database_new.php
- [ ] 同様に本番環境用DB情報に変更

### すべてのビューファイル（app/views/**/*.php）
- [ ] `/attendance-v2/public/css/` → `/css/`
- [ ] `/attendance-v2/public/index.php/` → `/`
- [ ] すべての `href` と `action` のパスを確認

---

## 🗄️ データベース

- [ ] Xserver でデータベース作成
- [ ] Xserver でデータベースユーザー作成
- [ ] ユーザーにアクセス権限付与
- [ ] phpMyAdmin で SQL インポート
- [ ] テーブルが正しく作成されたか確認

---

## 🔧 .htaccess 作成

- [ ] `public_html/.htaccess` を作成
- [ ] URL リライト設定を記述
- [ ] ファイルのアクセス権限を確認（644）

---

## ✅ 動作確認

### 基本動作
- [ ] `http://yourdomain.com/` でトップページ表示
- [ ] `http://yourdomain.com/login` でログインページ表示
- [ ] ログインが成功する
- [ ] メニューページが表示される
- [ ] CSS が正しく読み込まれている

### 管理者機能
- [ ] ユーザー一覧が表示される
- [ ] シフトカレンダーが表示される
- [ ] FullCalendar が動作する
- [ ] シフトの作成・編集・削除ができる
- [ ] 報酬一覧が表示される
- [ ] お知らせが表示される

### スタッフ機能
- [ ] 全体シフトが表示される
- [ ] 自分のシフトが表示される
- [ ] 日付詳細が表示される
- [ ] 自分の報酬が表示される

### 深夜シフト
- [ ] 月間ビューで1ブロック表示
- [ ] 週間ビューで27:00表示
- [ ] 報酬計算が正しい

### API
- [ ] `/api/shifts/all` が JSON を返す
- [ ] `/api/shifts/my` が JSON を返す

---

## 🔒 セキュリティ確認

- [ ] `/config/database.php` に直接アクセスできない
- [ ] `/app/` に直接アクセスできない
- [ ] `/core/` に直接アクセスできない
- [ ] エラーメッセージが本番用になっている

---

## 🎯 最終確認

- [ ] すべての機能が正常に動作する
- [ ] デザインが崩れていない
- [ ] エラーが発生していない
- [ ] パフォーマンスが許容範囲内
- [ ] バックアップを取得済み

---

**完了日**: _______________

**確認者**: _______________
