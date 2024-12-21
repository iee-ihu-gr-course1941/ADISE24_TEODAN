<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$username='root';
$password='';
$host='localhost';
$dbname = 'ataxx_game';


$mysqli = new mysqli($host, $username, $password, $dbname,null,'/home/student/iee/2021/iee2021233/mysql/run/mysql.sock');
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . 
    $mysqli->connect_errno . ") " . $mysqli->connect_error;
$action = $_POST['action'] ?? '';
}
switch ($action) {
    case 'new_game':
        newGame();
        break;
    case 'make_move':
        makeMove();
        break;
    case 'get_valid_moves':
        getValidMoves();
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}

function newGame() {
    global $pdo;

    $board = array_fill(0, 7, array_fill(0, 7, 0));
    $board[0][0] = 1;
    $board[6][6] = 2;
    $boardState = json_encode($board);

    try {
        $stmt = $pdo->prepare("INSERT INTO games (board_state, current_player) VALUES (?, ?)");
        $stmt->execute([$boardState, 1]);
        $gameID = $pdo->lastInsertId();
        echo json_encode(['game_id' => $gameID, 'board_state' => $boardState, 'current_player' => 1]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Failed to create new game: ' . $e->getMessage()]);
    }
}

function makeMove() {
    global $pdo;

    $gameID = $_POST['game_id'];
    $fromRow = $_POST['from_row'];
    $fromCol = $_POST['from_col'];
    $toRow = $_POST['to_row'];
    $toCol = $_POST['to_col'];

    $game = fetchGame($gameID);
    if (!$game) {
        echo json_encode(['error' => 'Game not found']);
        return;
    }

    $board = json_decode($game['board_state'], true);
    $currentPlayer = $game['current_player'];

    if ($board[$fromRow][$fromCol] !== $currentPlayer) {
        echo json_encode(['error' => 'You can only move your own pieces']);
        return;
    }

    $distance = max(abs($toRow - $fromRow), abs($toCol - $fromCol));
    if ($distance < 1 || $distance > 2 || $board[$toRow][$toCol] !== 0) {
        echo json_encode(['error' => 'Invalid move']);
        return;
    }

    $board[$toRow][$toCol] = $currentPlayer;
    if ($distance === 2) {
        $board[$fromRow][$fromCol] = 0;
    }

    for ($dr = -1; $dr <= 1; $dr++) {
        for ($dc = -1; $dc <= 1; $dc++) {
            $adjRow = $toRow + $dr;
            $adjCol = $toCol + $dc;

            if (isInBounds($adjRow, $adjCol) && $board[$adjRow][$adjCol] === 3 - $currentPlayer) {
                $board[$adjRow][$adjCol] = $currentPlayer;
            }
        }
    }

    $nextPlayer = 3 - $currentPlayer;

    if (isGameOver($board, $nextPlayer)) {
        $winner = determineWinner($board);
        echo json_encode(['game_over' => true, 'winner' => $winner]);
        return;
    }

    try {
        $stmt = $pdo->prepare("UPDATE games SET board_state = ?, current_player = ? WHERE id = ?");
        $stmt->execute([json_encode($board), $nextPlayer, $gameID]);
        echo json_encode(['board_state' => json_encode($board), 'current_player' => $nextPlayer]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Failed to update game: ' . $e->getMessage()]);
    }
}

function getValidMoves() {
    $row = $_POST['row'];
    $col = $_POST['col'];
    $gameID = $_POST['game_id'];

    $game = fetchGame($gameID);
    if (!$game) {
        echo json_encode(['error' => 'Game not found']);
        return;
    }

    $board = json_decode($game['board_state'], true);

    $validMoves = [];
    for ($dr = -2; $dr <= 2; $dr++) {
        for ($dc = -2; $dc <= 2; $dc++) {
            if ($dr === 0 && $dc === 0) continue;

            $newRow = $row + $dr;
            $newCol = $col + $dc;

            if (isInBounds($newRow, $newCol) && $board[$newRow][$newCol] === 0) {
                $validMoves[] = ['row' => $newRow, 'col' => $newCol];
            }
        }
    }

    echo json_encode(['valid_moves' => $validMoves]);
}

function isInBounds($row, $col) {
    return $row >= 0 && $row < 7 && $col >= 0 && $col < 7;
}

function isGameOver($board, $player) {
    foreach ($board as $row => $cols) {
        foreach ($cols as $col => $piece) {
            if ($piece === $player) {
                $validMoves = getValidMovesForPiece($board, $row, $col);
                if (count($validMoves) > 0) {
                    return false;
                }
            }
        }
    }
    return true;
}

function getValidMovesForPiece($board, $row, $col) {
    $validMoves = [];
    for ($dr = -2; $dr <= 2; $dr++) {
        for ($dc = -2; $dc <= 2; $dc++) {
            if ($dr === 0 && $dc === 0) continue;

            $newRow = $row + $dr;
            $newCol = $col + $dc;

            if (isInBounds($newRow, $newCol) && $board[$newRow][$newCol] === 0) {
                $validMoves[] = ['row' => $newRow, 'col' => $newCol];
            }
        }
    }
    return $validMoves;
}



function determineWinner($board) {
    $counts = [0, 0, 0];

    foreach ($board as $row) {
        foreach ($row as $cell) {
            $counts[$cell]++;
        }
    }

    return $counts[1] > $counts[2] ? 1 : 2;
}

function fetchGame($gameID) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM games WHERE id = ?");
    $stmt->execute([$gameID]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
