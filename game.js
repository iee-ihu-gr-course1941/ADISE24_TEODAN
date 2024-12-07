$(document).ready(function () {
    const boardElement = $('#board');
    const statusElement = $('#status');
    const size = 7;
    let gameID = null;
    let selectedCell = null;
    $('#startBtn').click(function() {
        $('#menu').fadeOut(500);
        $('body').addClass('game-active');
        startGame();
    });

    $('#instructionsBtn').click(function() {
        $('#instructionsModal').fadeIn(500);
    });

    $('#closeInstructionsBtn').click(function() {
        $('#instructionsModal').fadeOut(500);
    });

    $('#exitBtn').click(function() {
        window.close();
    });

    function initializeGame() {
        $.ajax({
            url: 'game.php',
            method: 'POST',
            data: { action: 'new_game' },
            success: function (response) {
                const data = JSON.parse(response);
                gameID = data.game_id;
                updateBoard(data.board_state, data.current_player);
                statusElement.text(`Player ${data.current_player}'s turn`);
            }
        });
    }

    boardElement.on('click', '.cell', function () {
        const row = $(this).data('row');
        const col = $(this).data('col');
    
        if (selectedCell) {
            $.ajax({
                url: 'game.php',
                method: 'POST',
                data: {
                    action: 'make_move',
                    game_id: gameID,
                    from_row: selectedCell.row,
                    from_col: selectedCell.col,
                    to_row: row,
                    to_col: col
                },
                success: function (response) {
                    const data = JSON.parse(response);
    
                    if (data.error) {
                        alert(data.error);
                        clearSelection();
                        return;
                    }
    
                    if (data.game_over) {
                        alert(`Game Over! Player ${data.winner} wins!`);
                        initializeGame();
                    } else {
                        updateBoard(data.board_state, data.current_player);
                        selectedCell = null;
                        statusElement.text(`Player ${data.current_player}'s turn`);
                    }
                }
            });
        } else {
            const piece = $(this).hasClass('player1') || $(this).hasClass('player2');
            if (piece) {
                selectedCell = { row, col };
                highlightValidMoves(row, col);
            }
        }
    });
    
    function highlightValidMoves(row, col) {
        clearHighlights();
    
        $.ajax({
            url: 'game.php',
            method: 'POST',
            data: {
                action: 'get_valid_moves',
                game_id: gameID,
                row: row,
                col: col
            },
            success: function(response) {
                const data = JSON.parse(response);
    
                if (data && data.valid_moves && Array.isArray(data.valid_moves)) {
                    data.valid_moves.forEach(move => {
                        const cell = $(`.cell[data-row="${move.row}"][data-col="${move.col}"]`);
                        if (cell.length > 0) {
                            cell.addClass('highlight');
                        }
                    });
                } else {
                    console.log('No valid moves or invalid response:', data);
                }
            },
            error: function() {
                console.log('Error fetching valid moves');
            }
        });
    }
    
    function clearHighlights() {
        $('.cell').removeClass('highlight');
    }

    function clearSelection() {
        selectedCell = null;
        clearHighlights();
    }

    function updateBoard(boardState, currentPlayer) {
        const board = JSON.parse(boardState);
        boardElement.empty();
    
        for (let row = 0; row < size; row++) {
            for (let col = 0; col < size; col++) {
                const cell = $('<div class="cell"></div>')
                    .attr('data-row', row)
                    .attr('data-col', col);
    
                if (board[row][col] === 1) {
                    cell.addClass('player1');
                } else if (board[row][col] === 2) {
                    cell.addClass('player2');
                }
    
                boardElement.append(cell);
            }
        }
    
        clearHighlights();
    }

    initializeGame();
});
