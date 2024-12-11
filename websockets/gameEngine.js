exports.createGameEngine = () => {
    const initGame = (gameFromDB, columns, rows) => {
        gameFromDB.gameStatus = null
        // statuses:
        // 0 - Game is pending
        
        gameFromDB.currentPlayer = Math.random() < 0.5 ? 1 : 2
        gameFromDB.board = Array(columns * rows).fill(0)
        gameFromDB.columns = columns
        gameFromDB.rows = rows
        gameFromDB.flippedCards = []
        return gameFromDB
    }

    // ------------------------------------------------------
    // Actions / Methods
    // ------------------------------------------------------

    // Check if the board is complete (no further plays are possible)
    const isBoardComplete = (game) => game.board.every(card => card !== 0)

    // returns whether the game has ended or not
    const gameEnded = (game) => game.gameStatus > 0

    // Check if the board is complete and change the gameStatus accordingly
    const changeGameStatus = (game) => {
        if (isBoardComplete(game)) {
            const player1Score = game.board.filter(card => card === 1).length
            const player2Score = game.board.filter(card => card === 2).length
            if (player1Score > player2Score) {
                game.gameStatus = 1
            } else if (player2Score > player1Score) {
                game.gameStatus = 2
            } else {
                game.gameStatus = 3
            }
        } else {
            game.gameStatus = 0
        }
    }

    // Plays a specific piece of the game (defined by its index)
    // Returns true if the game play is valid or an object with an error code and message otherwise
    const play = (game, index, playerSocketId) => {
        if ((playerSocketId != game.player1SocketId) && (playerSocketId != game.player2SocketId)){
            return {
                errorCode: 10,
                errorMessage: 'You are not playing this game!'
            }
        }
        if (gameEnded(game)) {
            return {
                errorCode: 11,
                errorMessage: 'Game has already ended!'
            }
        }
        if (game.board[index] !== 0) {
            return {
                errorCode: 13,
                errorMessage: 'Invalid play: card is already found!'
            }
        }
        game.flippedCards.push(index)
        if (game.flippedCards.length === 2) {
            const [firstIndex, secondIndex] = game.flippedCards
            if (game.board[firstIndex] === game.board[secondIndex]) {
                game.board[firstIndex] = game.currentPlayer
                game.board[secondIndex] = game.currentPlayer
            }
            game.flippedCards = []
            game.currentPlayer = game.currentPlayer === 1 ? 2 : 1
        }
        changeGameStatus(game)
        return true
    }

    // One of the players quits the game. The other one wins the game
    const quit = (game, playerSocketId) => {
        if ((playerSocketId != game.player1SocketId) && (playerSocketId != game.player2SocketId)){
            return {
                errorCode: 10,
                errorMessage: 'You are not playing this game!'
            }
        }
        if (gameEnded(game)) {
            return {
                errorCode: 11,
                errorMessage: 'Game has already ended!'
            }
        }
        game.gameStatus = playerSocketId == game.player1SocketId ? 2 : 1
        game.status = 'ended'
        return true
    }

    // Check if socket can close the game (game must have ended and player must belong to game)
    const close = (game, playerSocketId) => {
        if ((playerSocketId != game.player1SocketId) && (playerSocketId != game.player2SocketId)){
            return {
                errorCode: 10,
                errorMessage: 'You are not playing this game!'
            }
        }
        if (!gameEnded(game)) {
            return {
                errorCode: 14,
                errorMessage: 'Cannot close a game that has not ended!'
            }
        }
        return true
    }
    
    return {
        initGame,
        gameEnded,
        play,
        quit,
        close
    }
}