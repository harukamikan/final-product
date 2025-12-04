<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>目標を編集</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }
        input[type="text"],
        input[type="date"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }
        textarea {
            resize: vertical;
            min-height: 80px;
        }
        .button-group {
            text-align: center;
            margin-top: 30px;
        }
        button {
            padding: 12px 40px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #45a049;
        }
        .cancel-btn {
            background-color: #999;
            margin-left: 10px;
        }
        .cancel-btn:hover {
            background-color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>目標を編集</h1>
        
        <form action="{{ route('goals.update', $goal->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label for="title">タイトル</label>
                <input type="text" id="title" name="title" value="{{ old('title', $goal->title) }}" placeholder="例：～についてのブログ" required>
            </div>
            
            <div class="form-group">
                <label for="deadline">期限</label>
                <input type="date" id="deadline" name="deadline" value="{{ old('deadline', $goal->deadline) }}" required>
            </div>
            
            <div class="form-group">
                <label for="category">カテゴリ</label>
                <select id="category" name="category" required>
                    <option value="">選択してください</option>
                    <option value="ブログ" {{ old('category', $goal->category) == 'ブログ' ? 'selected' : '' }}>ブログ</option>
                    <option value="登壇" {{ old('category', $goal->category) == '登壇' ? 'selected' : '' }}>登壇</option>
                    <option value="資格" {{ old('category', $goal->category) == '資格' ? 'selected' : '' }}>資格</option>
                    <option value="イベント運営" {{ old('category', $goal->category) == 'イベント運営' ? 'selected' : '' }}>イベント運営</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="target_value">数値目標</label>
                <input type="text" id="target_value" name="target_value" value="{{ old('target_value', $goal->target_value) }}" placeholder="例：毎日単語100個、月20回ジムなど">
            </div>
            
            <div class="form-group">
                <label for="current_value">現状値</label>
                <input type="number" id="current_value" name="current_value" value="{{ old('current_value', $goal->current_value) }}" placeholder="初期値">
            </div>
            
            <div class="form-group">
                <label for="criteria">達成基準</label>
                <input type="text" id="criteria" name="criteria" value="{{ old('criteria', $goal->criteria) }}" placeholder="何を達成すると「完了」とみなすか">
            </div>
            
            <div class="form-group">
                <label for="memo">メモ</label>
                <textarea id="memo" name="memo" placeholder="自由記述">{{ old('memo', $goal->memo) }}</textarea>
            </div>
            
            <div class="button-group">
                <button type="submit">更新する</button>
                <button type="button" class="cancel-btn" onclick="window.history.back()">キャンセル</button>
            </div>
        </form>
    </div>
</body>
</html>
