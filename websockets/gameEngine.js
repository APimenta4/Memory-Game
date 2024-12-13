exports.createGameEngine = () => {
  const initGame = (newGame) => {
    newGame.gameStatus = 0;
    // statuses:
    // 0 - Playing
    // 1 - Player 1 wins
    // 2 - Player 2 wins
    newGame.currentPlayer = Math.random() < 0.5 ? 1 : 2;
    // We have to split the string because the board size is received as a YxZ
    const [rows, columns] = newGame.board_size.split("x").map(Number);
    const totalCards = rows * columns;

    const pairs = Array.from({ length: totalCards / 2 }, (_, i) => i + 1);
    let cardsNumber = [...pairs, ...pairs].sort(() => Math.random() - 0.5);
    
    newGame.cards = []
    cardsNumber.forEach(cardNumber => {
        newGame.cards.push({value:cardNumber,isFlipped: false,isMatched:false})
    });

    newGame.flippedCards = [];
    return newGame;
  };

  // ------------------------------------------------------
  // Actions / Methods
  // ------------------------------------------------------

  // Check if the board is complete (no further plays are possible)
  const isBoardComplete = (game) => game.cards.every((card) => card !== 0);

  // returns whether the game has ended or not
  const gameEnded = (game) => game.gameStatus > 0;

  // Check if the board is complete and change the gameStatus accordingly
  const changeGameStatus = (game) => {
    if (isBoardComplete(game)) {
      const player1Score = game.cards.filter((card) => card === 1).length;
      const player2Score = game.cards.filter((card) => card === 2).length;
      if (player1Score > player2Score) {
        game.gameStatus = 1;
      } else if (player2Score > player1Score) {
        game.gameStatus = 2;
      } else {
        // TODO Must untie
        game.gameStatus = 3;
      }
    } else {
      game.gameStatus = 0;
    }
  };

  // Plays a specific piece of the game (defined by its index)
  // Returns true if the game play is valid or an object with an error code and message otherwise
  const play = (game, index1, index2, playerSocketId) => {
    if (
      playerSocketId != game.player1SocketId &&
      playerSocketId != game.player2SocketId
    ) {
      return {
        errorCode: 10,
        errorMessage: "You are not playing this game!",
      };
    }
    if (gameEnded(game)) {
      return {
        errorCode: 11,
        errorMessage: "Game has already ended!",
      };
    }
    if (
      (game.currentPlayer == 1 && playerSocketId != game.player1SocketId) ||
      (game.currentPlayer == 2 && playerSocketId != game.player2SocketId)
    ) {
      return {
        errorCode: 12,
        errorMessage: "Invalid play: It is not your turn!",
      };
    }
    if (game.cards[index1] !== 0 || game.cards[index2] !== 0) {
      return {
        errorCode: 13,
        errorMessage:
          "Invalid play: one/both of the cards is/are already found!",
      };
    }
    if (game.cards[index1] === game.cards[index2]) {
      game.cards[index1] = game.currentPlayer;
      game.cards[index2] = game.currentPlayer;
      game.flippedCards.push(index1);
      game.flippedCards.push(index2);
    } else {
      game.currentPlayer = game.currentPlayer === 1 ? 2 : 1;
    }

    changeGameStatus(game);
    return true;
  };

  // One of the players quits the game. The other one wins the game
  const quit = (game, playerSocketId) => {
    if (
      playerSocketId != game.player1SocketId &&
      playerSocketId != game.player2SocketId
    ) {
      return {
        errorCode: 10,
        errorMessage: "You are not playing this game!",
      };
    }
    if (gameEnded(game)) {
      return {
        errorCode: 11,
        errorMessage: "Game has already ended!",
      };
    }
    game.gameStatus = playerSocketId == game.player1SocketId ? 2 : 1;
    game.status = "ended";
    return true;
  };

  // Check if socket can close the game (game must have ended and player must belong to game)
  const close = (game, playerSocketId) => {
    if (
      playerSocketId != game.player1SocketId &&
      playerSocketId != game.player2SocketId
    ) {
      return {
        errorCode: 10,
        errorMessage: "You are not playing this game!",
      };
    }
    if (!gameEnded(game)) {
      return {
        errorCode: 14,
        errorMessage: "Cannot close a game that has not ended!",
      };
    }
    return true;
  };

  return {
    initGame,
    gameEnded,
    play,
    quit,
    close,
  };
};