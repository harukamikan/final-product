# 🚀 Half Way

Half Way は、エンジニア組織における「目標管理の形骸化」と「アウトプット継続の難しさ」を解決するために開発された、社内向けWebアプリケーションです。  
半期目標と日々の行動を結びつけ、エンジニアの成長とナレッジ共有を自然に促進します。

本プロダクトでは、特に次の2つの課題に着目しました。

1. **Excelによる半期目標管理のしづらさ**  
半期目標がExcelで管理されていることで、日常業務の中で目に触れる機会が少なく、  
振り返りのタイミングでしか確認されない状態になっています。  
その結果、目標と日々の行動が分断されているという課題があります。

2. **経験をナレッジ化するモチベーションの低さ**  
技術ブログ執筆やイベント登壇などのアウトプット文化はあるものの、  
成果が可視化されにくく、評価や達成感を得にくいため、継続につながりにくい状況があります。


### 対象ユーザー
主に、以下のような悩みを抱える若手エンジニアを想定しています。

- 半期目標と日々の行動が結びついていない
- 技術ブログを書きたいと思いつつ、継続できていない
- イベント登壇に興味はあるが、経験がなく不安がある
- 他チームのノウハウや取り組みにアクセスしづらい

---

## 🔗 リンク

- **デプロイURL**: `https://final-product-production.up.railway.app/login` 
- **仕様書**: `https://docs.google.com/spreadsheets/d/1V4f1-4k_600-600/edit?gid=0#gid=0`(実際のpdfを添付)
- **デモ動画**: `https://youtu.be/your-demo-video` （デモ動画URLに差し替え）
- **Figmaデザイン**: `https://www.figma.com/board/4sVcqJ3m8NI0KfBO1vVmsM/phase04_Fusic?node-id=0-1&p=f` 

---

## ✨ 機能一覧(まとめ)

### 会社管理（マルチテナント）
-  会社作成・参加機能
-  自動生成された招待リンクによる招待（招待コード入力UIは廃止）
-  プロフィールからいつでも招待リンクをコピー可能

### オンボーディング
-  新規登録 → 会社選択/作成 → アンケート → ユーザー分類（segment）
-  アンケート項目：ロール / やりたいアウトプット / 現在の忙しさ / 経験年数
-  回答内容に基づく初期ミッション自動生成（パーソナライズ）

### ミッション管理（4種類）
-  **技術ブログ（Qiita）**: URL送信で完了
-  **イベント企画・開催**: フォーム送信で完了
-  **イベント登壇**: フォーム送信で完了
-  **資格取得**: フォーム送信で完了
-  `required_count` / `current_count` による必要回数管理（例：上級Tech Leadはブログ3本）

### ダッシュボード画面
-  総マイル、ランク、今月の活動表示
-  マイル獲得グラフの表示（/stats の Chart.js グラフをダッシュボードにも統合）

### ミッション一覧画面
-  会社ミッション・個人ミッションの進捗確認
-  ミッション完了画面
-  完了済みミッション画面
-  ブログURL送信、または各種フォーム送信で達成報告

### タイムライン画面
-  メンバーが登録した記事を一覧表示
-  AI要約表示（Gemini API使用）
-  カードグリッドUI（2カラムレイアウト）

### 統計画面
-  マイル獲得の推移グラフ（Chart.js）

### 活動履歴画面
-  会社ミッション・個人ミッションの進捗確認
-  過去の半期目標を表示

### ランキング画面
-  会社内のマイルランキング表示

### ガチャ画面
-  マイルを使ってガチャを引く

### プロフィール画面
-  基本情報（ユーザー名、メールアドレス、所属会社、Slack User ID、ニックネーム）を表示
-  通知設定
-  表示設定
-  招待リンク生成・再生成
-  セキュリティ設定
  - アカウント削除
  - パスワード変更

### 管理者画面
-  ユーザー管理
-  ミッション管理
-  報酬決定
-  報酬配布管理
-  半期目標一括アップロード
-  AI自動抽出アップロード
-  半期設定
-  管理者設定

### Slack連携
-  Slackログイン / Slackユーザー紐付け（`slack_id`）
-  Slash Command `/mission` でミッション完了機能（実装中・不具合対応中）
-  リマインド機能


## 🛠️ 技術スタック

| カテゴリ | 技術 |
|----------|------|
| **Backend** | Laravel |
| **Database** | MySQL |
| **Frontend** | Blade / Tailwind CSS / Alpine.js / Chart.js |
| **Authentication** | Laravel Breeze / Laravel Socialite |
| **Infrastructure** | Docker / Laravel Sail |
| **Deploy** | Railway |
| **Integrations** | Slack API / Qiita API / Gemini API / Claude API |
| **Dependencies** | Laravel Excel / SocialiteProviders/Slack |

---

## 🚀 開発環境のセットアップ

### 必要な環境
- Docker Desktop
- Git
- Composer

### セットアップ手順

```bash
# 1. リポジトリのクローン
git clone https://github.com/your-org/final-product.git
cd final-product

# 2. 依存関係のインストール
# Composer がローカルにない場合は以下を実行
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs

# または Composer がローカルにある場合
composer install

# 3. 環境変数ファイルの作成
cp .env.example .env

# 4. .env を編集し、必要な認証情報を設定
# - Slack API キー
# - Qiita API トークン
# - Gemini API キー
# など（後述の環境変数セクション参照）

# 5. アプリケーションキーの生成
./vendor/bin/sail artisan key:generate

# 6. Docker コンテナの起動
./vendor/bin/sail up -d

# 7. データベースマイグレーション & シーディング
./vendor/bin/sail artisan migrate --seed

# 8. ストレージリンクの作成
./vendor/bin/sail artisan storage:link

# 9. フロントエンドアセットのビルド
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

### アクセス

- ローカル環境: `http://localhost`
- phpMyAdmin（DB管理）: `http://localhost:8080`（設定により異なる）

---

## 📜 利用可能なコマンド

### Sail（Docker）コマンド

```bash
# コンテナ起動
./vendor/bin/sail up -d

# コンテナ停止
./vendor/bin/sail down

# Artisan コマンド実行
./vendor/bin/sail artisan <command>

# Composer コマンド
./vendor/bin/sail composer <command>

# NPM コマンド
./vendor/bin/sail npm <command>

# テスト実行
./vendor/bin/sail test
```

### Artisan コマンド

```bash
# マイグレーション
./vendor/bin/sail artisan migrate

# マイグレーションのロールバック
./vendor/bin/sail artisan migrate:rollback

# シーダー実行
./vendor/bin/sail artisan db:seed

# キャッシュクリア
./vendor/bin/sail artisan cache:clear
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan route:clear
./vendor/bin/sail artisan view:clear

# デバッグ用マイル付与（開発環境のみ）
./vendor/bin/sail artisan tinker
# >>> App\Models\User::find(1)->increment('miles', 100);
```

### 開発用スクリプト

```bash
# すべての開発サーバーを同時起動（server, queue, logs, vite）
./vendor/bin/sail composer dev

# テスト実行
./vendor/bin/sail composer test
```

---

## 🔑 環境変数

`.env` ファイルに以下の情報を設定してください。

### 基本設定

```env
APP_NAME=OutputHub
APP_ENV=local
APP_KEY=（sail artisan key:generate で自動生成）
APP_DEBUG=true
APP_URL=http://localhost
```

### データベース設定（Laravel Sail / MySQL）

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
```

### Slack API（OAuth & Slash Command）

```env
# Slack OAuth（ログインに使用）
SLACK_CLIENT_ID=your_slack_client_id
SLACK_CLIENT_SECRET=your_slack_client_secret

# Slash Command 検証・Bot Token
SLACK_SIGNING_SECRET=your_slack_signing_secret
SLACK_BOT_TOKEN=xoxb-your-slack-bot-token
```

**取得方法**:
1. [Slack API Dashboard](https://api.slack.com/apps) でアプリを作成
2. OAuth & Permissions から `SLACK_CLIENT_ID` / `SLACK_CLIENT_SECRET` を取得
3. Basic Information から `SLACK_SIGNING_SECRET` を取得
4. Install App から `SLACK_BOT_TOKEN` を取得
