#!/bin/bash

# Test script for Slack /mission command
# This simulates a Slack slash command request

echo "🧪 Testing Slack /mission endpoint..."
echo ""

# Test 1: Signature verification (will fail without proper signature)
echo "Test 1: Sending /mission command (no signature - should return error)..."
curl -X POST http://localhost:8000/api/slack/commands \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "user_id=U12345&text=&command=/mission&response_url=https://hooks.slack.com/test" \
  -w "\nHTTP Status: %{http_code}\n"

echo ""
echo ""

# Test 2: With qiita subcommand
echo "Test 2: Sending /mission qiita command (no signature - should return error)..."
curl -X POST http://localhost:8000/api/slack/commands \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "user_id=U12345&text=qiita https://qiita.com/test&command=/mission&response_url=https://hooks.slack.com/test" \
  -w "\nHTTP Status: %{http_code}\n"

echo ""
echo "✅ Tests completed!"
echo ""
echo "Note: Without proper Slack signature, these will return validation errors."
echo "This is expected behavior. The important thing is:"
echo "  1. HTTP 200 status (not 500 or dispatch_failed)"
echo "  2. JSON response is returned"
echo ""
echo "To test with proper signature, use actual Slack workspace."
