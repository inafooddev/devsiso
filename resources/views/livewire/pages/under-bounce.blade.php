@push('styles')
<style>
    /* Animasi float dan slideIn */
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

    /* Styling untuk canvas game */
    canvas {
        background: #1f2937; /* Latar belakang gelap untuk area game */
        border-radius: 0.5rem;
        display: block;
        /* Membuat canvas responsif */
        max-width: 100%;
        height: auto;
    }
</style>
@endpush

<!-- Konten Game untuk Livewire/Blade -->
<div class="flex flex-col items-center justify-center min-h-[calc(100vh-12rem)]">
    <!-- Header -->
    <div class="text-center mb-6 slide-in">
        <h1 class="text-4xl font-bold text-gray-800 mb-2">🚀 Bounce Breakout</h1>
        <p class="text-gray-600">Hancurkan semua balok untuk menang! 🧱</p>
    </div>

    <!-- Start Screen (Layar Mulai) -->
    <div id="startScreen" class="text-center space-y-6">
        <div class="floating text-8xl mb-4">🏀</div>
        <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full">
            <h2 class="text-3xl font-bold text-purple-600 mb-4">Bounce Breakout</h2>
            <div class="space-y-3 text-left text-gray-700 mb-6">
                <p>⬅️ ➡️ <strong>Arrow Keys</strong> - Gerakkan dayung</p>
                <p>🅿️ <strong>Spasi</strong> - Jeda/Lanjutkan permainan</p>
                <p>🎯 <strong>Goal</strong> - Hancurkan semua balok!</p>
            </div>
            <button 
                onclick="startGame()"
                class="w-full px-8 py-4 bg-gradient-to-r from-purple-600 to-blue-600 text-blue text-xl font-bold rounded-lg hover:from-purple-700 hover:to-blue-700 transition transform hover:scale-105 shadow-lg">
                🎮 Mulai Bermain!
            </button>
        </div>
    </div>

    <!-- Game Screen (Layar Game) -->
    <div id="gameScreen" class="hidden">
        <div class="flex gap-6 flex-wrap justify-center">
            
            <!-- Game Board (Papan Game) -->
            <div class="bg-white rounded-2xl shadow-2xl p-4 sm:p-6">
                <!-- Canvas menggantikan div gameBoard dari Tetris -->
                <!-- Ukuran diatur oleh CSS dan JS agar lebih responsif -->
                <canvas id="gameCanvas" width="600" height="500"></canvas>
            </div>

            <!-- Side Panel (Panel Samping) -->
            <div class="space-y-4 w-full max-w-xs sm:w-auto sm:max-w-none">
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

                <!-- Lives (Menggantikan Lines) -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="text-sm text-gray-600 mb-1">Lives</div>
                    <div class="text-4xl font-bold text-green-600" id="lives">3</div>
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

    <!-- Game Over Screen (Layar Game Over) -->
    <div id="gameOverScreen" class="hidden text-center space-y-6">
        <div class="text-8xl mb-4">🏆</div>
        <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full">
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
                        <div class="text-2xl font-bold text-green-600" id="finalLives">0</div>
                        <div class="text-sm text-gray-600">Lives</div>
                    </div>
                </div>
            </div>
            <div class="space-y-3">
                <button 
                    onclick="startGame()"
                    class="w-full px-8 py-4 bg-gradient-to-r from-green-600 to-green-700 text-blue text-xl font-bold rounded-lg hover:from-green-700 hover:to-green-800 transition transform hover:scale-105 shadow-lg">
                    🔄 Play Again
                </button>
                <!-- Menggunakan sintaks Blade untuk 'Back' seperti contoh asli Anda -->
                <a 
                    href="{{ url()->previous() }}"
                    class="block w-full px-8 py-4 bg-gradient-to-r from-gray-600 to-gray-700 text-blue text-xl font-bold rounded-lg hover:from-gray-700 hover:to-gray-800 transition transform hover:scale-105 shadow-lg">
                    ← Back
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Memastikan skrip tidak berjalan ganda jika komponen dimuat ulang
    if (typeof gameLoopId === 'undefined') {
        var canvas, ctx, score, level, lives, isPaused, gameLoopId, rightPressed, leftPressed;
        var ball, paddle, brick, bricks;

        // Fungsi untuk menginisialisasi variabel saat DOM siap
        function initGameVariables() {
            canvas = document.getElementById('gameCanvas');
            // Cek jika canvas tidak ditemukan (mungkin di halaman lain)
            if (!canvas) return false;
            
            ctx = canvas.getContext('2d');
            
            score = 0;
            level = 1;
            lives = 3;
            isPaused = false;
            gameLoopId = null;
            rightPressed = false;
            leftPressed = false;

            // Properti Bola
            ball = {
                x: canvas.width / 2,
                y: canvas.height - 30,
                dx: 4, 
                dy: -4,
                radius: 10,
                color: '#00f0f0'
            };

            // Properti Dayung
            paddle = {
                height: 12,
                width: 100,
                x: (canvas.width - 100) / 2,
                color: '#f0a000'
            };

            // Properti Balok
            brick = {
                rowCount: 5,
                columnCount: 9,
                width: 58,
                height: 20,
                padding: 5,
                offsetTop: 30,
                offsetLeft: 30,
                colors: ['#f00000', '#00f000', '#a000f0', '#f0f000', '#0000f0']
            };

            bricks = [];
            
            return true;
        }

        // --- Fungsi Inisialisasi ---
        function initBricks() {
            if (!canvas) return;
            bricks = [];
            for (let c = 0; c < brick.columnCount; c++) {
                bricks[c] = [];
                for (let r = 0; r < brick.rowCount; r++) {
                    bricks[c][r] = { x: 0, y: 0, status: 1, color: brick.colors[r % brick.colors.length] };
                }
            }
            ball.dx = 4 + (level - 1) * 0.5;
            ball.dy = -4 - (level - 1) * 0.5;
        }
        
        function resetBallAndPaddle() {
            if (!canvas) return;
            ball.x = canvas.width / 2;
            ball.y = canvas.height - 30;
            ball.dx = (4 + (level - 1) * 0.5) * (Math.random() < 0.5 ? 1 : -1);
            ball.dy = -4 - (level - 1) * 0.5;
            paddle.x = (canvas.width - paddle.width) / 2;
        }

        // --- Fungsi Menggambar (Rendering) ---
        function drawBall() {
            ctx.beginPath();
            ctx.arc(ball.x, ball.y, ball.radius, 0, Math.PI * 2);
            ctx.fillStyle = ball.color;
            ctx.fill();
            ctx.closePath();
        }

        function drawPaddle() {
            ctx.beginPath();
            ctx.rect(paddle.x, canvas.height - paddle.height, paddle.width, paddle.height);
            ctx.fillStyle = paddle.color;
            ctx.fill();
            ctx.closePath();
        }

        function drawBricks() {
            for (let c = 0; c < brick.columnCount; c++) {
                for (let r = 0; r < brick.rowCount; r++) {
                    if (bricks[c][r].status == 1) {
                        const brickX = c * (brick.width + brick.padding) + brick.offsetLeft;
                        const brickY = r * (brick.height + brick.padding) + brick.offsetTop;
                        bricks[c][r].x = brickX;
                        bricks[c][r].y = brickY;
                        
                        ctx.beginPath();
                        ctx.rect(brickX, brickY, brick.width, brick.height);
                        ctx.fillStyle = bricks[c][r].color;
                        ctx.fill();
                        ctx.closePath();
                    }
                }
            }
        }

        function drawGame() {
            if (!ctx) return;
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            drawBricks();
            drawBall();
            drawPaddle();
        }

        // --- Fungsi Logika Game ---
        function updateGame() {
            if (!canvas) return;
            if (rightPressed && paddle.x < canvas.width - paddle.width) {
                paddle.x += 7;
            } else if (leftPressed && paddle.x > 0) {
                paddle.x -= 7;
            }

            ball.x += ball.dx;
            ball.y += ball.dy;

            if (ball.x + ball.radius > canvas.width || ball.x - ball.radius < 0) {
                ball.dx = -ball.dx;
            }

            if (ball.y - ball.radius < 0) {
                ball.dy = -ball.dy;
            } 
            else if (ball.y + ball.radius > canvas.height) {
                if (ball.x > paddle.x && ball.x < paddle.x + paddle.width) {
                    ball.dy = -ball.dy;
                    let deltaX = ball.x - (paddle.x + paddle.width / 2);
                    ball.dx = deltaX * 0.15;
                } else {
                    lives--;
                    updateUI();
                    if (lives <= 0) {
                        gameOver();
                    } else {
                        resetBallAndPaddle();
                    }
                }
            }
            collisionDetection();
        }
        
        function collisionDetection() {
            let allBricksBroken = true;
            for (let c = 0; c < brick.columnCount; c++) {
                for (let r = 0; r < brick.rowCount; r++) {
                    const b = bricks[c][r];
                    if (b.status == 1) {
                        allBricksBroken = false; 
                        if (
                            ball.x > b.x &&
                            ball.x < b.x + brick.width &&
                            ball.y > b.y &&
                            ball.y < b.y + brick.height
                        ) {
                            ball.dy = -ball.dy; 
                            b.status = 0; 
                            score += 10;
                            updateUI();
                        }
                    }
                }
            }
            
            if (allBricksBroken) {
                level++;
                score += 1000; 
                lives++; 
                updateUI();
                initBricks();
                resetBallAndPaddle();
            }
        }
        
        // --- Game Loop Utama ---
        function gameLoop() {
            if (isPaused || !canvas) {
                return; 
            }
            updateGame();
            drawGame();
            gameLoopId = requestAnimationFrame(gameLoop);
        }

        // --- Kontrol UI & Game State (dibuat global) ---
        window.updateUI = function() {
            document.getElementById('score').textContent = score;
            document.getElementById('level').textContent = level;
            document.getElementById('lives').textContent = lives;
        }

        window.startGame = function() {
            // Inisialisasi variabel jika belum
            if (!initGameVariables()) {
                console.error("Canvas not found. Game cannot start.");
                return;
            }
            
            score = 0;
            level = 1;
            lives = 3;
            isPaused = false;
            
            initBricks();
            resetBallAndPaddle();
            updateUI();
            
            document.getElementById('startScreen').classList.add('hidden');
            document.getElementById('gameOverScreen').classList.add('hidden');
            document.getElementById('gameScreen').classList.remove('hidden');
            document.getElementById('pauseBtn').textContent = '⏸️ Pause';
            
            if (gameLoopId) cancelAnimationFrame(gameLoopId);
            gameLoop();
        }

        window.togglePause = function() {
            if (!canvas) return;
            isPaused = !isPaused;
            const btn = document.getElementById('pauseBtn');
            btn.textContent = isPaused ? '▶️ Resume' : '⏸️ Pause';
            
            if (!isPaused) {
                gameLoop();
            }
        }

        window.quitGame = function() {
            if (gameLoopId) cancelAnimationFrame(gameLoopId);
            gameLoopId = null; // Hentikan loop
            document.getElementById('gameScreen').classList.add('hidden');
            document.getElementById('startScreen').classList.remove('hidden');
        }

        window.gameOver = function() {
            if (gameLoopId) cancelAnimationFrame(gameLoopId);
            gameLoopId = null; // Hentikan loop
            
            document.getElementById('gameScreen').classList.add('hidden');
            document.getElementById('gameOverScreen').classList.remove('hidden');
            
            document.getElementById('finalScore').textContent = score;
            document.getElementById('finalLevel').textContent = level;
            document.getElementById('finalLives').textContent = lives > 0 ? lives : 0;
        }

        // --- Event Listeners untuk Kontrol ---
        // Tambahkan listener hanya sekali
        document.addEventListener('keydown', (e) => {
            if (!canvas || document.getElementById('gameScreen').classList.contains('hidden')) return;

            if (e.key == 'Right' || e.key == 'ArrowRight') {
                rightPressed = true;
            } else if (e.key == 'Left' || e.key == 'ArrowLeft') {
                leftPressed = true;
            } else if (e.key == ' ' || e.key == 'Spacebar') {
                e.preventDefault(); 
                togglePause();
            }
        });

        document.addEventListener('keyup', (e) => {
            if (e.key == 'Right' || e.key == 'ArrowRight') {
                rightPressed = false;
            } else if (e.key == 'Left' || e.key == 'ArrowLeft') {
                leftPressed = false;
            }
        });

        // Menambahkan listener ke window saat DOM dimuat
        document.addEventListener('DOMContentLoaded', () => {
            // Coba inisialisasi, mungkin elemen sudah ada
            if(initGameVariables()) {
                 // Menambahkan listener sentuh setelah canvas diinisialisasi
                canvas.addEventListener('touchstart', handleTouchMove, { passive: false });
                canvas.addEventListener('touchmove', handleTouchMove, { passive: false });
            }
        });
        
        // Listener untuk Livewire (jika komponen di-render ulang)
        document.addEventListener('livewire:load', () => {
             if(initGameVariables()) {
                canvas.addEventListener('touchstart', handleTouchMove, { passive: false });
                canvas.addEventListener('touchmove', handleTouchMove, { passive: false });
            }
        });
        
        // Fungsi handle sentuh
        function handleTouchMove(e) {
            if (!canvas) return;
            e.preventDefault(); // Mencegah scroll
            
            // Menghitung offset canvas dengan benar
            const canvasRect = canvas.getBoundingClientRect();
            const scaleX = canvas.width / canvasRect.width;
            
            const relativeX = (e.touches[0].clientX - canvasRect.left) * scaleX;
            
            if (relativeX > 0 && relativeX < canvas.width) {
                paddle.x = relativeX - paddle.width / 2;
                if (paddle.x < 0) paddle.x = 0;
                if (paddle.x + paddle.width > canvas.width) paddle.x = canvas.width - paddle.width;
            }
        }
    }
</script>
@endpush