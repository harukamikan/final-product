# Railway Deployment - Slack Integration Setup

## Required Environment Variables

The following environment variables must be set in your Railway project for Slack integration to work properly:

### Slack OAuth & Bot Configuration

```bash
# Slack App Credentials (from https://api.slack.com/apps)
SLACK_CLIENT_ID=your_client_id
SLACK_CLIENT_SECRET=your_client_secret
SLACK_REDIRECT_URI=https://your-railway-app.up.railway.app/auth/slack/callback

# Slack Signing Secret (for verifying slash commands)
SLACK_SIGNING_SECRET=your_signing_secret

# Slack Bot Token (for posting messages)
SLACK_BOT_USER_OAUTH_TOKEN=xoxb-your-bot-token
```

### How to Get These Values

1. **Create a Slack App** (if not already created)
   - Go to https://api.slack.com/apps
   - Click "Create New App" → "From scratch"
   - Name your app and select your workspace

2. **Get Client ID & Client Secret**
   - In your Slack App settings, go to "Basic Information"
   - Under "App Credentials", you'll find:
     - Client ID
     - Client Secret
     - Signing Secret

3. **Get Bot User OAuth Token**
   - Go to "OAuth & Permissions"
   - Under "Bot Token Scopes", add required scopes:
     - `chat:write` (to send messages)
     - `commands` (to use slash commands)
     - `users:read` (to read user info)
     - `users:read.email` (to read user email)
   - Click "Install to Workspace"
   - Copy the "Bot User OAuth Token" (starts with `xoxb-`)

4. **Configure OAuth Redirect URL**
   - In "OAuth & Permissions", add your redirect URL:
     - `https://your-railway-app.up.railway.app/auth/slack/callback`

5. **Configure Slash Command**
   - Go to "Slash Commands"
   - Click "Create New Command"
   - Command: `/mission`
   - Request URL: `https://your-railway-app.up.railway.app/api/slack/commands`
   - Short Description: `ミッションを達成する`
   - Click "Save"

## Queue Worker Setup

Since the Slack command processing uses Laravel queues, you need to run a queue worker on Railway:

### Option 1: Add a Worker Process

In your Railway project, add a new service or process:

```bash
php artisan queue:work --tries=3 --timeout=60
```

### Option 2: Use Cron (for low traffic)

If queue volume is low, you can run the queue worker periodically:

```bash
# Add to your start command
php artisan queue:work --once
```

## Verifying the Setup

1. **Check Environment Variables**
   ```bash
   # On Railway, verify all Slack variables are set
   echo $SLACK_SIGNING_SECRET
   ```

2. **Check Logs**
   ```bash
   # Watch Railway logs for incoming Slack requests
   tail -f storage/logs/laravel.log
   ```

3. **Test the Slash Command**
   - In Slack, type: `/mission`
   - You should see a menu with buttons
   - Try: `/mission qiita https://qiita.com/example`
   - Check logs for "Slack command received" and "Processing Slack mission command"

## Troubleshooting

### "dispatch_failed" Error

**Cause**: Slack didn't receive a 200 OK response within 3 seconds

**Solutions**:
- ✅ Verify queue worker is running
- ✅ Check Railway logs for errors
- ✅ Ensure `SLACK_SIGNING_SECRET` is set correctly
- ✅ Verify Request URL in Slack app matches your Railway URL

### Signature Verification Failed

**Cause**: `SLACK_SIGNING_SECRET` is incorrect or missing

**Solutions**:
- Check the value matches exactly from Slack App settings → Basic Information → Signing Secret
- Ensure no extra spaces or newlines in the environment variable

### Queue Jobs Not Processing

**Cause**: No queue worker running

**Solutions**:
- Start a queue worker process on Railway
- Check `QUEUE_CONNECTION` is set to `database` (default)
- Run migrations: `php artisan migrate`

## Production Checklist

- [ ] All Slack environment variables set in Railway
- [ ] Queue worker running as separate process
- [ ] Database migrations run (`php artisan migrate`)
- [ ] Slack app OAuth redirect URL configured
- [ ] Slack slash command request URL configured
- [ ] Test `/mission` command in Slack workspace
- [ ] Monitor logs for errors
