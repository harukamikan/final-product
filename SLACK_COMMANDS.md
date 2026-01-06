# Slack Bot - Mission Commands Reference

## Available Commands

### 1. `/mission`

表示：ミッションメニュー（ボタン付き）

**使い方**:
```
/mission
```

**応答**:
- イベント企画・開催フォームへのボタン
- イベント登壇フォームへのボタン  
- 資格取得フォームへのボタン

---

### 2. `/mission qiita <URL>`

Qiita記事でミッション完了

**使い方**:
```
/mission qiita https://qiita.com/your-article
```

**応答**:
```
🔄 Qiitaミッションを処理中です...
（完了通知は数秒後に表示されます）
```

その後:
```
✅ Qiitaミッションを完了しました！（URL受領）
🎉 50マイルを獲得しました！
```

**エラー例**:
```
/mission qiita invalid-url
```
→ `URLが正しくないかもです。例：/mission qiita https://qiita.com/...`

---

## ユーザー連携について

Slackコマンドを使う前に、**Webアプリで一度Slackログイン**が必要です。

### 連携手順:
1. https://final-product-production.up.railway.app/login にアクセス
2. 「Slackでログイン」をクリック
3. Slack認証を許可
4. ダッシュボードが表示されたら連携完了✅

### 未連携の場合:
```
ユーザー連携が見つかりませんでした。
まずWebアプリでSlackログインしてから再度お試しください。
```

---

## ミッションの種類

| ミッション | Slack完了方法 | Web完了方法 |
|----------|-------------|-----------|
| 技術ブログ(Qiita) | `/mission qiita <URL>` | Qiita URL送信 |
| イベント企画・開催 | `/mission` → ボタン → フォーム | アプリ内フォーム |
| イベント登壇 | `/mission` → ボタン → フォーム | アプリ内フォーム |
| 資格取得 | `/mission` → ボタン → フォーム | アプリ内フォーム |

---

## トラブルシューティング

### Q: `/mission` を実行しても何も表示されない

**A**: 以下を確認してください:
- Slack App が正しくインストールされているか
- Webアプリで一度Slackログインしたか
- Slack Workspace が正しいか

### Q: "dispatch_failed" エラーが出る

**A**: これは修正済みです。最新版にアップデートしてください。

### Q: Qiitaミッションが完了しない

**A**: URL形式を確認してください:
- ✅ 正しい: `https://qiita.com/username/items/...`
- ❌ 間違い: `qiita.com/...` (httpsなし)
- ❌ 間違い: `invalid-url`

### Q: 「処理中」のまま結果が表示されない

**A**: Queue Worker が起動していない可能性があります。
- Railway: Queue Worker プロセスが起動しているか確認
- ログを確認: `storage/logs/laravel.log`

---

## 管理者向け情報

### Slack App設定

- **App Name**: Final Product Mission Bot
- **Slash Command**: `/mission`
- **Request URL**: `https://final-product-production.up.railway.app/api/slack/commands`

### 必要な権限 (Scopes)

- `chat:write` - メッセージ送信
- `commands` - Slash Commands
- `users:read` - ユーザー情報取得
- `users:read.email` - メールアドレス取得

### 環境変数

```bash
SLACK_CLIENT_ID=...
SLACK_CLIENT_SECRET=...
SLACK_SIGNING_SECRET=...
SLACK_BOT_USER_OAUTH_TOKEN=xoxb-...
```

詳細は [`RAILWAY_SLACK_SETUP.md`](./RAILWAY_SLACK_SETUP.md) を参照。

---

## 今後の拡張案

- [ ] `/mission list` - 未達成ミッション一覧表示
- [ ] `/mission status` - 現在のマイル数と達成状況
- [ ] `/mission ranking` - ランキング表示
- [ ] リマインダー機能（週次で未達成ミッション通知）
