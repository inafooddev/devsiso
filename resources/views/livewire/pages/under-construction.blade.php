

    @push('styles')
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .floating {
            animation: float 3s ease-in-out infinite;
        }
        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .slide-in {
            animation: slideIn 0.3s ease-out;
        }
        .game-cell {
            width: 30px;
            height: 30px;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }
        .game-preview-cell {
            width: 20px;
            height: 20px;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .line-clear {
            animation: pulse 0.3s ease-in-out 2;
        }
    </style>
    @endpush

    <div class="flex flex-col items-center justify-center min-h-[calc(100vh-12rem)]">
        <!-- Header -->
        <div class="text-center mb-6 slide-in">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">🏗️ Under Construction</h1>
            <p class="text-gray-600">This page is being built. Play Tetris while you wait! 🎮</p>
        </div>

        <!-- Start Screen -->
        <div id="startScreen" class="text-center space-y-6">
            <div class="floating text-8xl mb-4">🧱</div>
            <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md">
                <h2 class="text-3xl font-bold text-purple-600 mb-4">Tetris Builder</h2>
                <div class="space-y-3 text-left text-gray-700 mb-6">
                    <p>⬅️ ➡️ <strong>Arrow Keys</strong> - Move left/right</p>
                    <p>⬆️ <strong>Up Arrow</strong> - Rotate piece</p>
                    <p>⬇️ <strong>Down Arrow</strong> - Drop faster</p>
                    <p>🎯 <strong>Goal</strong> - Clear lines to score!</p>
                </div>
                <button 
                    onclick="startGame()"
                    class="w-full px-8 py-4 bg-gradient-to-r from-purple-600 to-blue-600 text-blue text-xl font-bold rounded-lg hover:from-purple-700 hover:to-blue-700 transition transform hover:scale-105 shadow-lg">
                    🎮 Start Building!
                </button>
            </div>
        </div>

        <!-- Game Screen -->
        <div id="gameScreen" class="hidden">
            <div class="flex gap-6 flex-wrap justify-center">
                <!-- Game Board -->
                <div class="bg-white rounded-2xl shadow-2xl p-6">
                    <div id="gameBoard" class="bg-gray-900 rounded-lg" style="display: inline-block;"></div>
                </div>

                <!-- Side Panel -->
                <div class="space-y-4">
                    <!-- Score -->
                    <div class="bg-white rounded-xl shadow-lg p-6 min-w-[200px]">
                        <div class="text-sm text-gray-600 mb-1">Score</div>
                        <div class="text-4xl font-bold text-purple-600" id="score">0</div>
                    </div>

                    <!-- Level -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <div class="text-sm text-gray-600 mb-1">Level</div>
                        <div class="text-4xl font-bold text-blue-600" id="level">1</div>
                    </div>

                    <!-- Lines -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <div class="text-sm text-gray-600 mb-1">Lines</div>
                        <div class="text-4xl font-bold text-green-600" id="lines">0</div>
                    </div>

                    <!-- Next Piece -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <div class="text-sm text-gray-600 mb-2">Next</div>
                        <div id="nextPiece" class="bg-gray-100 rounded-lg p-2"></div>
                    </div>

                    <!-- Controls -->
                    <div class="bg-white rounded-xl shadow-lg p-4 space-y-2">
                        <button 
                            onclick="togglePause()"
                            id="pauseBtn"
                            class="w-full px-4 py-2 bg-yellow-500 text-white font-bold rounded-lg hover:bg-yellow-600 transition">
                            ⏸️ Pause
                        </button>
                        <button 
                            onclick="quitGame()"
                            class="w-full px-4 py-2 bg-red-500 text-white font-bold rounded-lg hover:bg-red-600 transition">
                            🚪 Quit
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Game Over Screen -->
        <div id="gameOverScreen" class="hidden text-center space-y-6">
            <div class="text-8xl mb-4">🏆</div>
            <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Game Over!</h2>
                <div class="space-y-2 mb-6">
                    <div class="text-5xl font-bold text-purple-600" id="finalScore">0</div>
                    <div class="text-gray-600">Final Score</div>
                    <div class="flex justify-center gap-8 mt-4">
                        <div>
                            <div class="text-2xl font-bold text-blue-600" id="finalLevel">1</div>
                            <div class="text-sm text-gray-600">Level</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-green-600" id="finalLines">0</div>
                            <div class="text-sm text-gray-600">Lines</div>
                        </div>
                    </div>
                </div>
                <div class="space-y-3">
                    <button 
                        onclick="startGame()"
                        class="w-full px-8 py-4 bg-gradient-to-r from-green-600 to-green-700 text-white text-xl font-bold rounded-lg hover:from-green-700 hover:to-green-800 transition transform hover:scale-105 shadow-lg">
                        🔄 Play Again
                    </button>
                    <a 
                        href="{{ url()->previous() }}"
                        class="block w-full px-8 py-4 bg-gradient-to-r from-gray-600 to-gray-700 text-white text-xl font-bold rounded-lg hover:from-gray-700 hover:to-gray-800 transition transform hover:scale-105 shadow-lg">
                        ← Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const COLS = 10;
        const ROWS = 20;
        const BLOCK_SIZE = 30;
        
        let board = [];
        let score = 0;
        let level = 1;
        let lines = 0;
        let currentPiece = null;
        let nextPiece = null;
        let gameLoop = null;
        let isPaused = false;
        let dropSpeed = 1000;

        const COLORS = {
            I: '#00f0f0',
            O: '#f0f000',
            T: '#a000f0',
            S: '#00f000',
            Z: '#f00000',
            J: '#0000f0',
            L: '#f0a000'
        };

        const SHAPES = {
            I: [[1,1,1,1]],
            O: [[1,1],[1,1]],
            T: [[0,1,0],[1,1,1]],
            S: [[0,1,1],[1,1,0]],
            Z: [[1,1,0],[0,1,1]],
            J: [[1,0,0],[1,1,1]],
            L: [[0,0,1],[1,1,1]]
        };

        function initBoard() {
            board = Array(ROWS).fill().map(() => Array(COLS).fill(0));
        }

        function drawBoard() {
            const boardEl = document.getElementById('gameBoard');
            boardEl.innerHTML = '';
            boardEl.style.display = 'grid';
            boardEl.style.gridTemplateColumns = `repeat(${COLS}, ${BLOCK_SIZE}px)`;
            boardEl.style.gap = '0';

            for (let row = 0; row < ROWS; row++) {
                for (let col = 0; col < COLS; col++) {
                    const cell = document.createElement('div');
                    cell.className = 'game-cell';
                    cell.style.backgroundColor = board[row][col] || '#1f2937';
                    boardEl.appendChild(cell);
                }
            }

            if (currentPiece) {
                drawPiece(currentPiece);
            }
        }

        function drawPiece(piece) {
            const boardEl = document.getElementById('gameBoard');
            const cells = boardEl.children;

            for (let row = 0; row < piece.shape.length; row++) {
                for (let col = 0; col < piece.shape[row].length; col++) {
                    if (piece.shape[row][col]) {
                        const boardRow = piece.y + row;
                        const boardCol = piece.x + col;
                        if (boardRow >= 0 && boardRow < ROWS && boardCol >= 0 && boardCol < COLS) {
                            const index = boardRow * COLS + boardCol;
                            cells[index].style.backgroundColor = COLORS[piece.type];
                        }
                    }
                }
            }
        }

        function drawNextPiece() {
            const nextEl = document.getElementById('nextPiece');
            nextEl.innerHTML = '';
            
            const maxSize = Math.max(nextPiece.shape.length, nextPiece.shape[0].length);
            nextEl.style.display = 'grid';
            nextEl.style.gridTemplateColumns = `repeat(${maxSize}, 20px)`;
            nextEl.style.gap = '0';

            for (let row = 0; row < maxSize; row++) {
                for (let col = 0; col < maxSize; col++) {
                    const cell = document.createElement('div');
                    cell.className = 'game-preview-cell';
                    if (row < nextPiece.shape.length && col < nextPiece.shape[row].length && nextPiece.shape[row][col]) {
                        cell.style.backgroundColor = COLORS[nextPiece.type];
                    } else {
                        cell.style.backgroundColor = '#f3f4f6';
                    }
                    nextEl.appendChild(cell);
                }
            }
        }

        function createPiece() {
            const types = Object.keys(SHAPES);
            const type = types[Math.floor(Math.random() * types.length)];
            return {
                type: type,
                shape: SHAPES[type].map(row => [...row]),
                x: Math.floor(COLS / 2) - Math.floor(SHAPES[type][0].length / 2),
                y: 0
            };
        }

        function canMove(piece, offsetX, offsetY) {
            for (let row = 0; row < piece.shape.length; row++) {
                for (let col = 0; col < piece.shape[row].length; col++) {
                    if (piece.shape[row][col]) {
                        const newX = piece.x + col + offsetX;
                        const newY = piece.y + row + offsetY;
                        
                        if (newX < 0 || newX >= COLS || newY >= ROWS) {
                            return false;
                        }
                        if (newY >= 0 && board[newY][newX]) {
                            return false;
                        }
                    }
                }
            }
            return true;
        }

        function mergePiece() {
            for (let row = 0; row < currentPiece.shape.length; row++) {
                for (let col = 0; col < currentPiece.shape[row].length; col++) {
                    if (currentPiece.shape[row][col]) {
                        const boardRow = currentPiece.y + row;
                        const boardCol = currentPiece.x + col;
                        if (boardRow >= 0) {
                            board[boardRow][boardCol] = COLORS[currentPiece.type];
                        }
                    }
                }
            }
        }

        function rotatePiece() {
            const rotated = currentPiece.shape[0].map((_, i) =>
                currentPiece.shape.map(row => row[i]).reverse()
            );
            
            const oldShape = currentPiece.shape;
            currentPiece.shape = rotated;
            
            if (!canMove(currentPiece, 0, 0)) {
                currentPiece.shape = oldShape;
            }
        }

        function clearLines() {
            let linesCleared = 0;
            
            for (let row = ROWS - 1; row >= 0; row--) {
                if (board[row].every(cell => cell !== 0)) {
                    board.splice(row, 1);
                    board.unshift(Array(COLS).fill(0));
                    linesCleared++;
                    row++;
                }
            }
            
            if (linesCleared > 0) {
                lines += linesCleared;
                score += linesCleared * 100 * level;
                
                if (lines >= level * 2) {
                    level++;
                    dropSpeed = Math.max(100, 1000 - (level - 1) * 100);
                    clearInterval(gameLoop);
                    gameLoop = setInterval(drop, dropSpeed);
                }
                
                updateScore();
            }
        }

        function drop() {
            if (isPaused) return;
            
            if (canMove(currentPiece, 0, 1)) {
                currentPiece.y++;
            } else {
                mergePiece();
                clearLines();
                currentPiece = nextPiece;
                nextPiece = createPiece();
                drawNextPiece();
                
                if (!canMove(currentPiece, 0, 0)) {
                    gameOver();
                    return;
                }
            }
            drawBoard();
        }

        function updateScore() {
            document.getElementById('score').textContent = score;
            document.getElementById('level').textContent = level;
            document.getElementById('lines').textContent = lines;
        }

        function startGame() {
            initBoard();
            score = 0;
            level = 1;
            lines = 0;
            dropSpeed = 600;
            isPaused = false;
            
            currentPiece = createPiece();
            nextPiece = createPiece();
            
            document.getElementById('startScreen').classList.add('hidden');
            document.getElementById('gameOverScreen').classList.add('hidden');
            document.getElementById('gameScreen').classList.remove('hidden');
            document.getElementById('pauseBtn').textContent = '⏸️ Pause';
            
            updateScore();
            drawBoard();
            drawNextPiece();
            
            if (gameLoop) clearInterval(gameLoop);
            gameLoop = setInterval(drop, dropSpeed);
        }

        function togglePause() {
            isPaused = !isPaused;
            const btn = document.getElementById('pauseBtn');
            btn.textContent = isPaused ? '▶️ Resume' : '⏸️ Pause';
        }

        function quitGame() {
            if (confirm('Are you sure you want to quit? Your progress will be lost.')) {
                clearInterval(gameLoop);
                document.getElementById('gameScreen').classList.add('hidden');
                document.getElementById('startScreen').classList.remove('hidden');
            }
        }

        function gameOver() {
            clearInterval(gameLoop);
            document.getElementById('gameScreen').classList.add('hidden');
            document.getElementById('gameOverScreen').classList.remove('hidden');
            document.getElementById('finalScore').textContent = score;
            document.getElementById('finalLevel').textContent = level;
            document.getElementById('finalLines').textContent = lines;
        }

        document.addEventListener('keydown', (e) => {
            if (!currentPiece || isPaused || document.getElementById('gameScreen').classList.contains('hidden')) return;
            
            switch(e.key) {
                case 'ArrowLeft':
                    e.preventDefault();
                    if (canMove(currentPiece, -1, 0)) {
                        currentPiece.x--;
                    }
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                    if (canMove(currentPiece, 1, 0)) {
                        currentPiece.x++;
                    }
                    break;
                case 'ArrowDown':
                    e.preventDefault();
                    if (canMove(currentPiece, 0, 1)) {
                        currentPiece.y++;
                        score += 1;
                        updateScore();
                    }
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    rotatePiece();
                    break;
                case ' ':
                    e.preventDefault();
                    togglePause();
                    break;
            }
            drawBoard();
        });
    </script>
    @endpush
