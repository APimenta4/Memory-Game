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

    newGame.flippedCardsIndex = [];
    newGame.player1Score = 0;
    newGame.player2Score = 0;
    newGame.lastPairDiscoveredBy = null;
    return newGame;
  };

  // ------------------------------------------------------
  // Actions / Methods
  // ------------------------------------------------------

  // Check if the board is complete (no further plays are possible)
  const isBoardComplete = (game) => game.cards.every((card) => card.isMatched);

  // returns whether the game has ended or not
  const gameEnded = (game) => game.gameStatus > 0;

  // Check if the board is complete and change the gameStatus accordingly
  const changeGameStatus = (game) => {
    if (isBoardComplete(game)) {
      if (game.player1Score > game.player2Score) {
        game.gameStatus = 1;
      } else if (game.player2Score > game.player1Score) {
        game.gameStatus = 2;
      } else {
        game.gameStatus = game.lastPairDiscoveredBy == game.player1SocketId ? 2 : 1;
      }
    } else {
      game.gameStatus = 0;
    }
  };

  // Plays a specific piece of the game (defined by its index)
  // Returns true if the game play is valid or an object with an error code and message otherwise
  const play = (game, index, playerSocketId) => {
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
    if (game.flippedCardsIndex.includes(index)) {
      return {
        errorCode: 13,
        errorMessage:
          "Invalid play: You cannot play a card that has already been flipped!",
      };
    }
    
    if (game.flippedCardsIndex.length % 2 === 0) {
      // first flip
      game.cards[index].isFlipped = true;
      game.flippedCardsIndex.push(index);
    } else {
      // second flip
      const previousIndex = game.flippedCardsIndex[game.flippedCardsIndex.length - 1];
      // match
      if (game.cards[previousIndex].value === game.cards[index].value) {
        game.flippedCardsIndex.push(index);
        game.cards[index].isFlipped = true;
        game.cards[index].isMatched = true; 
        game.cards[previousIndex].isMatched = true;  
        if (playerSocketId == game.player1SocketId) {
          game.player1Score++;
        } else {
          game.player2Score++;
        }
        game.lastPairDiscoveredBy = playerSocketId;
        changeGameStatus(game);
        return true;
      }
      // no match
      game.cards[index].isFlipped = true;    
      game.flippedCardsIndex.splice(game.flippedCardsIndex.indexOf(previousIndex), 1);
      game.currentPlayer = game.currentPlayer == 1 ? 2 : 1;
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

  const flipDownCards = (game) => {
    game.cards.forEach((card) => {
      card.isFlipped = false;
    });
    game.flippedCardsIndex = [];
  };

  return {
    initGame,
    gameEnded,
    play,
    quit,
    close,
    flipDownCards,
  };
};