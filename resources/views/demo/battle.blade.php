<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>バトルロイヤル</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            text-align: center;
        }

        h1 {
            color: white;
            font-size: 3em;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .battlefield {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            padding: 40px;
            min-height: 600px;
            position: relative;
        }

        .stickman-container {
            display: inline-block;
            margin: 20px;
            text-align: center;
            position: absolute; 
            transition: all 0.5s ease;
        }

        .stickman {
            width: 60px;
            height: 120px;
            position: relative;
            display: inline-block;
        }

        .name {
            font-weight: bold;
            margin-top: 10px;
            font-size: 14px;
        }

        .hp {
            color: #666;
            font-size: 12px;
        }

        .start-button {
            margin-top: 30px;
            padding: 15px 40px;
            font-size: 20px;
            background: #ff4757;
            color: white;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(255, 71, 87, 0.4);
            transition: transform 0.2s;
        }

        .start-button:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎮 報酬バトルロイヤル</h1>
        
        <div class="battlefield" id="battlefield">
            <!-- JavaScriptで生成 -->
        </div>

        <button class="start-button" onclick="startBattle()">⚔️ バトル開始！</button>
    </div>

    <script>
        // 参加者データ（テスト用）
        const participants = [
            { name: 'haruka', miles: 1500 },
            { name: 'tanaka', miles: 1200 },
            { name: 'sato', miles: 1100 },
            { name: 'yamada', miles: 1000 },
            { name: 'abe', miles: 800 },
            { name: 'suzuki', miles: 750 },
            { name: 'watanabe', miles: 700 },
            { name: 'ito', miles: 650 },
            { name: 'kobayashi', miles: 600 },
            { name: 'kato', miles: 550 },
            { name: 'yoshida', miles: 500 },
            { name: 'nakamura', miles: 450 },
        ];

        // 棒人間を生成
        function createStickman(person) {
            return `
                <div class="stickman-container">
                    <svg class="stickman" viewBox="0 0 60 120">
                        <circle cx="30" cy="20" r="15" fill="#333" stroke="#000" stroke-width="2"/>
                        <line x1="30" y1="35" x2="30" y2="75" stroke="#333" stroke-width="3"/>
                        <line x1="30" y1="45" x2="10" y2="65" stroke="#333" stroke-width="3"/>
                        <line x1="30" y1="45" x2="50" y2="65" stroke="#333" stroke-width="3"/>
                        <line x1="30" y1="75" x2="15" y2="110" stroke="#333" stroke-width="3"/>
                        <line x1="30" y1="75" x2="45" y2="110" stroke="#333" stroke-width="3"/>
                    </svg>
                    <div class="name">${person.name}</div>
                    <div class="hp">${person.miles} HP</div>
                </div>
            `;
        }

        // 初期化
        // 初期化（円形配置）
        function init() {
            const battlefield = document.getElementById('battlefield');
            const radius = 200; // 円の半径
            const centerX = battlefield.offsetWidth / 2;
            const centerY = battlefield.offsetHeight / 2;
            
            participants.forEach((person, index) => {
                const angle = (index / participants.length) * 2 * Math.PI;
                const x = centerX + radius * Math.cos(angle) - 30; // -30は棒人間の幅の半分
                const y = centerY + radius * Math.sin(angle) - 60; // -60は棒人間の高さの半分
                
                const container = document.createElement('div');
                container.className = 'stickman-container';
                container.style.left = `${x}px`;
                container.style.top = `${y}px`;
                container.innerHTML = `
                    <svg class="stickman" viewBox="0 0 60 120">
                        <circle cx="30" cy="20" r="15" fill="#333" stroke="#000" stroke-width="2"/>
                        <line x1="30" y1="35" x2="30" y2="75" stroke="#333" stroke-width="3"/>
                        <line x1="30" y1="45" x2="10" y2="65" stroke="#333" stroke-width="3"/>
                        <line x1="30" y1="45" x2="50" y2="65" stroke="#333" stroke-width="3"/>
                        <line x1="30" y1="75" x2="15" y2="110" stroke="#333" stroke-width="3"/>
                        <line x1="30" y1="75" x2="45" y2="110" stroke="#333" stroke-width="3"/>
                    </svg>
                    <div class="name">${person.name}</div>
                    <div class="hp">${person.miles} HP</div>
                `;
                
                battlefield.appendChild(container);
            });
        }
        // バトル開始
        async function startBattle() {
            const button = document.querySelector('.start-button');
            button.disabled = true;
            button.textContent = 'バトル中...';
            
            // 全員を中央に集める
            const containers = document.querySelectorAll('.stickman-container');
            const centerX = document.getElementById('battlefield').offsetWidth / 2 - 30;
            const centerY = document.getElementById('battlefield').offsetHeight / 2 - 60;
            
            containers.forEach(container => {
                container.style.left = `${centerX + (Math.random() - 0.5) * 100}px`;
                container.style.top = `${centerY + (Math.random() - 0.5) * 100}px`;
            });
            
            // 少し待つ
            await sleep(1000);
            
            // マイルが少ない順に脱落（下位から順に）
            const sorted = [...participants].sort((a, b) => a.miles - b.miles);
            
            // 下位の人たちを脱落させる（上位12人を残す）
            for (let i = 0; i < sorted.length - 12; i++) {
                await sleep(500); // 0.5秒ごとに脱落
                
                const person = sorted[i];
                const index = participants.indexOf(person);
                containers[index].classList.add('eliminated');
                
                console.log(`${person.name} 脱落！`);
            }
            
            // 完了
            await sleep(1000);
            button.textContent = '🎉 バトル終了！';
            alert('上位12人が生き残りました！');
        }

        // sleep関数（待機用）
        function sleep(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }

        // ページ読み込み時に実行
        init();
    </script>
</body>
</html>