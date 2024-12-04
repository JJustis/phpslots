

<!DOCTYPE html>
<html>
<head>
    <title>ReservePHP Slots Game</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            text-align: center;
            background: #f5f5f5;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin: 20px 0;
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .cell {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            font-size: 2em;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s;
        }
        .cell.spinning {
            transform: scale(0.95);
        }
        button {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1em;
            transition: all 0.3s;
        }
        button:disabled {
            background: #cccccc;
            cursor: not-allowed;
        }
        button:hover:not(:disabled) {
            background: #45a049;
            transform: translateY(-2px);
        }
        .message {
            margin: 20px 0;
            font-size: 1.2em;
            font-weight: bold;
            min-height: 1.5em;
            color: #333;
        }
        .stats {
            margin-bottom: 20px;
            font-size: 1.1em;
            background: white;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        @keyframes winPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        .win-animation {
            animation: winPulse 0.5s ease-in-out;
        }
        .username-input {
            padding: 8px;
            margin: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1em;
        }
    </style>
</head>
<body>
    <h1>ReservePHP Slots Game</h1>
    
    <div class="stats">
        <input type="text" id="username" class="username-input" placeholder="Enter username" required>
        <br>
        Experience: <span id="experience">0</span> XP
    </div>
    
    <div class="grid" id="grid">
        <?php for ($i = 0; $i < 9; $i++): ?>
            <div class="cell">❓</div>
        <?php endfor; ?>
    </div>
    
    <button id="spinButton">Spin!</button>
    <div class="message" id="message"></div>

    <script>
        const grid = document.getElementById('grid');
        const cells = grid.getElementsByClassName('cell');
        const spinButton = document.getElementById('spinButton');
        const messageEl = document.getElementById('message');
        const experienceEl = document.getElementById('experience');
        const usernameInput = document.getElementById('username');
        let isSpinning = false;
        
        async function spin() {
            if (isSpinning) return;
            
            const username = usernameInput.value.trim();
            if (!username) {
                messageEl.textContent = 'Please enter a username';
                return;
            }
            
            isSpinning = true;
            spinButton.disabled = true;
            messageEl.textContent = '';
            
            const spinDuration = 2000;
            const spinInterval = 100;
            
            try {
                // Start spinning animation
                const spinAnimation = setInterval(() => {
                    for (let cell of cells) {
                        cell.classList.add('spinning');
                        cell.textContent = ['🍎', '🍋', '🍇', '🍒', '💎', '7️⃣'][Math.floor(Math.random() * 6)];
                    }
                }, spinInterval);

                // Send spin request
                const formData = new FormData();
                formData.append('username', username);
                
                const response = await fetch('game.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.error) {
                    messageEl.textContent = result.error;
                    return;
                }
                
                // Continue animation briefly
                await new Promise(resolve => setTimeout(resolve, 1000));
                clearInterval(spinAnimation);
                
                // Show final result
                result.grid.forEach((symbol, index) => {
                    cells[index].textContent = symbol;
                    cells[index].classList.remove('spinning');
                });
                
                experienceEl.textContent = result.totalExp;
                
                if (result.wins > 0) {
                    messageEl.textContent = `🎉 You won! +${result.expGained} XP`;
                    grid.classList.add('win-animation');
                    setTimeout(() => grid.classList.remove('win-animation'), 500);
                } else {
                    messageEl.textContent = 'Try again!';
                }
                
            } catch (error) {
                messageEl.textContent = 'Error occurred. Please try again.';
                console.error('Error:', error);
            } finally {
                isSpinning = false;
                spinButton.disabled = false;
            }
        }

        spinButton.addEventListener('click', spin);
    </script>
</body>
</html>