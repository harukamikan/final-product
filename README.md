# 🚀 OutputHub

エンジニア組織のアウトプット活動を促進・可視化する社内向けWebアプリケーション。ミッション達成でマイルを獲得し、ランクや進捗をチーム全体で可視化できます。

技術ブログの投稿、イベント企画・登壇、資格取得など、エンジニアの成長につながるアウトプットを習慣化し、組織全体のナレッジ共有を活性化します。

---

## 🔗 リンク

- **デプロイURL**: `https://your-app.railway.app` （本番環境のURLに差し替え）
- **デモ動画**: `https://youtu.be/your-demo-video` （デモ動画URLに差し替え）
- **Figmaデザイン**: `https://figma.com/file/your-design` （FigmaリンクURLに差し替え）

---

## ✨ 機能一覧 (MVP)

### 会社管理（マルチテナント）
- ✅ 会社作成・参加機能
- ✅ 自動生成された招待リンクによる招待（招待コード入力UIは廃止）
- ✅ プロフィールからいつでも招待リンクをコピー可能

### オンボーディング
- ✅ 新規登録 → 会社選択/作成 → アンケート → ユーザー分類（segment）
- ✅ アンケート項目：ロール / やりたいアウトプット / 現在の忙しさ / 経験年数
- ✅ 回答内容に基づく初期ミッション自動生成（パーソナライズ）

### ミッション管理（4種類）
- ✅ **技術ブログ（Qiita）**: URL送信で完了
- ✅ **イベント企画・開催**: フォーム送信で完了
- ✅ **イベント登壇**: フォーム送信で完了
- ✅ **資格取得**: フォーム送信で完了
- ✅ `required_count` / `current_count` による必要回数管理（例：上級Tech Leadはブログ3本）

### ダッシュボード
- ✅ 総マイル、ランク、今月の活動表示
- ✅ マイル獲得グラフの表示（/stats の Chart.js グラフをダッシュボードにも統合）

### Qiitaタイムライン
- ✅ メンバーが登録したQiita記事を一覧表示
- ✅ AI要約表示（Gemini API使用）
- ✅ カードグリッドUI（2カラムレイアウト）

### Slack連携
- ✅ Slackログイン / Slackユーザー紐付け（`slack_id`）
- 🔧 Slash Command `/mission` でミッション完了機能（実装中・不具合対応中）

---

## 機能一覧（まとめ）

### 一般ユーザー向け

| 画面 | 説明 |
|------|------|
| **ダッシュボード** | 総マイル、ランク、今月の活動、マイル獲得グラフを表示 |
| **ミッション一覧** | 自分専用ミッション・共有ミッションの進捗確認 |
| **ミッション完了** | ブログURL送信、または各種フォーム送信で達成報告 |
| **完了済みミッション** | 過去の達成履歴とマイル獲得履歴を確認 |
| **Qiitaタイムライン** | チームメンバーのQiita記事をAI要約付きで一覧表示 |
| **プロフィール** | 個人情報編集、招待リンク生成・再生成 |
| **ランキング** | 会社内のマイルランキング表示 |
| **統計（Stats）** | マイル獲得の推移グラフ（Chart.js） |
| **アクティビティ** | 会社全体の活動ログを時系列で表示 |

### 管理者向け

| 画面 | 説明 |
|------|------|
| **管理ダッシュボード** | 会社全体の活動状況・統計を確認 |
| **ミッション作成** | 新規ミッション・課題の作成・編集 |
| **目標アップロード** | CSV一括インポート、AI解析によるアップロード |
| **報酬管理** | 報酬の有効化・締め切り設定・配布管理 |
| **設定** | システム全般の設定変更 |

---

## 🛠️ 技術スタック

| カテゴリ | 技術 |
|----------|------|
| **Backend** | Laravel 12.x (PHP 8.4) |
| **Database** | MySQL (Laravel Sail / Docker) |
| **Frontend** | Blade, Tailwind CSS, Alpine.js, Chart.js |
| **Authentication** | Laravel Breeze, Laravel Socialite |
| **Infrastructure** | Docker, Laravel Sail (開発環境) |
| **Deploy** | Railway（本番環境） |
| **Integrations** | Slack API（OAuth、Slash Command）、Qiita API（記事取得）、Gemini API（AI要約） |
| **Dependencies** | Laravel Excel（CSV処理）、SocialiteProviders/Slack |

---

## 🚀 開発環境のセットアップ

### 必要な環境
- Docker Desktop
- Git
- Composer（任意：ローカルにインストールされていなくても Sail 経由で実行可能）

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

### Qiita API（記事取得）

```env
QIITA_ACCESS_TOKEN=your_qiita_access_token
```

**取得方法**:
1. [Qiita 設定ページ](https://qiita.com/settings/tokens) にアクセス
2. 「個人用アクセストークン」を発行

### Gemini API（AI要約）

```env
GEMINI_API_KEY=your_gemini_api_key
```

**取得方法**:
1. [Google AI Studio](https://makersuite.google.com/app/apikey) にアクセス
2. API キーを発行

---

## 📁 ディレクトリ構成

```
final-product/
├── app/
│   ├── Http/
│   │   ├── Controllers/          # コントローラー
│   │   │   ├── Admin/            # 管理者用コントローラー
│   │   │   ├── DashboardController.php
│   │   │   ├── MissionController.php
│   │   │   ├── QiitaArticleController.php
│   │   │   ├── SlackController.php
│   │   │   └── ...
│   │   └── Middleware/           # カスタムミドルウェア
│   ├── Models/                   # Eloquentモデル
│   │   ├── Company.php
│   │   ├── Mission.php
│   │   ├── User.php
│   │   ├── QiitaArticle.php
│   │   └── ...
│   ├── Helpers/                  # ヘルパー関数
│   │   └── RankHelper.php        # ランク計算ロジック
│   └── Services/                 # ビジネスロジック
│
├── database/
│   ├── migrations/               # マイグレーションファイル
│   ├── seeders/                  # シーダーファイル
│   └── factories/                # モデルファクトリ
│
├── resources/
│   ├── views/                    # Bladeテンプレート
│   │   ├── dashboard.blade.php
│   │   ├── missions/
│   │   ├── qiita/
│   │   ├── profile/
│   │   └── admin/
│   └── css/                      # スタイルシート
│
├── routes/
│   ├── web.php                   # Webルート定義
│   └── api.php                   # APIルート定義（Slack等）
│
├── config/                       # 設定ファイル
├── public/                       # 公開ディレクトリ
├── storage/                      # ログ、キャッシュ等
├── tests/                        # テストファイル
├── compose.yaml                  # Docker Compose設定（Sail）
└── .env.example                  # 環境変数テンプレート
```

---

## 📝 開発メモ

### ミッション管理の設計思想

- **個人ミッション**: `missions.user_id` に値があるミッションは、そのユーザー専用
- **共有ミッション**: `missions.user_id` が NULL のミッションは、会社全体で共有
- **進捗管理**: `user_missions` テーブルで各ユーザーの進捗（`current_count` / `required_count`）を管理

### マイルとランクの仕組み

- ミッション完了時に `reward_miles` が `users.miles` に加算される
- ランク判定は `RankHelper::getRank($miles)` で計算（Bronze → Silver → Gold → Platinum など）
- ダッシュボードや統計画面で可視化

### Slack連携の現状

- **実装済み**: Slackログイン（OAuth）、ユーザー紐付け（`slack_id`）
- **実装中**: Slash Command `/mission` によるミッション完了機能
  - `/api/slack/commands` でリクエストを受信
  - 現在、`dispatch_failed` エラーの対応中

---

## 🤝 コントリビューション

1. このリポジトリをフォーク
2. フィーチャーブランチを作成 (`git checkout -b feature/amazing-feature`)
3. 変更をコミット (`git commit -m 'Add some amazing feature'`)
4. ブランチにプッシュ (`git push origin feature/amazing-feature`)
5. プルリクエストを作成

---

## 📄 ライセンス

MIT License

---

## 📧 お問い合わせ

プロジェクトに関する質問や提案は、Issueまたは社内Slackチャンネルまでお願いします。
