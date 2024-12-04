<?php
// game.php
session_start();
header('Content-Type: application/json');

// Connect to ReservePHP database
$conn = new mysqli('localhost', 'root', '', 'reservesphp');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

class SlotsGame {
    private $symbols = ['🍎', '🍋', '🍇', '🍒', '💎', '7️⃣'];
    private $grid = [];
    
    public function spin() {
        $this->grid = [];
        for ($i = 0; $i < 9; $i++) {
            $this->grid[] = $this->symbols[array_rand($this->symbols)];
        }
        return $this->grid;
    }
    
    public function checkWins() {
        $winningLines = [
            [0, 1, 2], [3, 4, 5], [6, 7, 8], // Rows
            [0, 3, 6], [1, 4, 7], [2, 5, 8], // Columns
            [0, 4, 8], [2, 4, 6] // Diagonals
        ];
        
        $wins = 0;
        foreach ($winningLines as $line) {
            if ($this->grid[$line[0]] === $this->grid[$line[1]] && 
                $this->grid[$line[1]] === $this->grid[$line[2]]) {
                $wins++;
            }
        }
        return $wins;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
    $username = $conn->real_escape_string($_POST['username']);
    
    // First verify user exists
    $check = $conn->query("SELECT exp FROM members WHERE username = '$username' LIMIT 1");
    if ($check->num_rows === 0) {
        echo json_encode(['error' => 'Invalid user']);
        exit;
    }
    
    $game = new SlotsGame();
    $finalGrid = $game->spin();
    $wins = $game->checkWins();
    $expGained = $wins * 100;
    
    // Update experience if there's a win
    if ($wins > 0) {
        $conn->query("UPDATE members SET exp = exp + $expGained WHERE username = '$username'");
    }
    
    // Get updated experience
    $result = $conn->query("SELECT exp FROM members WHERE username = '$username'");
    $row = $result->fetch_assoc();
    
    echo json_encode([
        'grid' => $finalGrid,
        'wins' => $wins,
        'expGained' => $expGained,
        'totalExp' => $row['exp']
    ]);
    exit;
}
?>