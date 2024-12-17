const httpServer = require("http").createServer();
const io = require("socket.io")(httpServer, {
  cors: {
    origin: "*",
    methods: ["GET", "POST"],
    credentials: true,
  },
});

const PORT = process.env.APP_PORT || 8086;

const { createLobby } = require("./lobby");
const lobby = createLobby();
const { createUtil } = require("./util");
const util = createUtil();
const { createGameEngine } = require("./gameEngine");
const gameEngine = createGameEngine();

const turnTimers = new Map();

httpServer.listen(PORT, () => {
  console.log(`listening on localhost:${PORT}`);
});

io.on("connection", (socket) => {
  console.log(`client ${socket.id} has connected`);

  socket.on("echo", (message) => {
    //socket.emit("echo", message); devolve para o cliente - como está no deploy
    io.emit("echo", message); //devolve para todos os clientes
  });

  
  // ------------------------------------------------------
  // Notification Alert
  // ------------------------------------------------------
  socket.on("new_device", (userId) => {
  console.log(`New Device of User ${userId} connected with socket ID ${socket.id}`);
  socket.join(`user_${userId}`);
  });
  
  socket.on("notification_alert", (userId) => {
  console.log(`Notification Alert of User ${userId} connected with socket ID ${socket.id}`);
  const userRoom = `user_${userId}`;
  setTimeout(
      ()=>{
          io.to(userRoom).emit("notification");
          console.log(`Notification sent`);
      },
      1000
  )
  });
  // ------------------------------------------------------
  // Disconnect
  // ------------------------------------------------------
  // disconnection event is triggered when the client disconnects but is still on the rooms

  socket.on("disconnecting", (reason) => {
    socket.rooms.forEach((room) => {
      if (room === "lobby") {
        lobby.leaveLobby(socket.id);
        io.to("lobby").emit("lobbyChanged", lobby.getGames());
      }
    });
    util.getRoomGamesPlaying(socket).forEach(([roomName, room]) => {
      const game = room.game;
      if (!gameEngine.gameEnded(game)) {
        io.to(roomName).emit("gameInterrupted", game);
      }
      clearTurnTimer(roomName); // Clear the timer for this game
      socket.leave(roomName);
    });
  });

  // ------------------------------------------------------
  // User identity
  // ------------------------------------------------------

  socket.on("login", (user) => {
    // Stores user information on the socket as "user" property
    socket.data.user = user;
    if (user && user.id) {
      socket.join("user_" + user.id);
      socket.join("lobby");
    }
  });

  socket.on("logout", (user) => {
    if (user && user.id) {
      socket.leave("user_" + user.id);
      lobby.leaveLobby(socket.id);
      io.to("lobby").emit("lobbyChanged", lobby.getGames());
      socket.leave("lobby");
      util.getRoomGamesPlaying(socket).forEach(([roomName, room]) => {
        socket.leave(roomName);
        if (!gameEngine.gameEnded(room.game)) {
          io.to(roomName).emit("gameInterrupted", room.game);
        }
      });
    }
    socket.data.user = undefined;
  });

  // ------------------------------------------------------
  // Chat and Private Messages
  // ------------------------------------------------------

  socket.on("chatMessage", (message) => {
    const payload = {
      user: socket.data.user,
      message: message,
    };
    io.sockets.emit("chatMessage", payload);
  });

  socket.on("privateMessage", (clientMessageObj, callback) => {
    const destinationRoomName = "user_" + clientMessageObj?.destinationUser?.id;

    // Check if the destination user is online
    if (io.sockets.adapter.rooms.get(destinationRoomName)) {
      const payload = {
        user: socket.data.user,
        message: clientMessageObj.message,
      };
      // send the "privateMessage" to the destination user (using "his" room)
      io.to(destinationRoomName).emit("privateMessage", payload);
      if (callback) {
        callback({ success: true });
      }
    } else {
      if (callback) {
        callback({
          errorCode: 1,
          errorMessage: `User "${clientMessageObj?.destinationUser?.name}" is not online!`,
        });
      }
    }
  });

  // ------------------------------------------------------
  // Lobby
  // ------------------------------------------------------

  socket.on("fetchGames", (callback) => {
    if (!util.checkAuthenticatedUser(socket, callback)) {
      return;
    }
    const games = lobby.getGames();
    if (callback) {
      callback(games);
    }
  });

  socket.on("addGame", (gameDataId, cols, rows, callback) => {
    if (!util.checkAuthenticatedUser(socket, callback)) {
      return;
    }
    const game = lobby.addGame(
      socket.data.user.id,
      socket.data.user.nickname,
      socket.id,
      gameDataId,
      cols,
      rows
    );
    io.to("lobby").emit("lobbyChanged", lobby.getGames());
    if (callback) {
      callback(game);
    }
  });

  socket.on("joinGame", (id, callback) => {
    if (!util.checkAuthenticatedUser(socket, callback)) {
      return;
    }
    const game = lobby.getGame(id);
    if (socket.data.user.id == game.player1Id) {
      if (callback) {
        callback({
          errorCode: 3,
          errorMessage: "User cannot join a game that he created!",
        });
      }
      return;
    }
    game.player2Id = socket.data.user.id;
    game.player2SocketId = socket.id;
    game.player2Nickname = socket.data.user.nickname;
    lobby.removeGame(id);
    io.to("lobby").emit("lobbyChanged", lobby.getGames());
    if (callback) {
      callback(game);
    }
  });

  socket.on("removeGame", (id, callback) => {
    if (!util.checkAuthenticatedUser(socket, callback)) {
      return;
    }
    const game = lobby.getGame(id);
    if (socket.data.user.id != game.player1Id) {
      if (callback) {
        callback({
          errorCode: 4,
          errorMessage: "User cannot remove a game that he has not created!",
        });
      }
      return;
    }
    lobby.removeGame(game.id);
    io.to("lobby").emit("lobbyChanged", lobby.getGames());
    if (callback) {
      callback(game);
    }
  });

  // ------------------------------------------------------
  // Multiplayer Game
  // ------------------------------------------------------

  socket.on("startGame", (clientGame, callback) => {
    if (!util.checkAuthenticatedUser(socket, callback)) {
      return;
    }
    const roomName = "game_" + clientGame.id;
    const game = gameEngine.initGame(clientGame);
    // join the 2 players to the game room
    io.sockets.sockets.get(game.player1SocketId)?.join(roomName);
    io.sockets.sockets.get(game.player2SocketId)?.join(roomName);
    // store the game data directly on the room object:
    socket.adapter.rooms.get(roomName).game = game;
    // emit the "gameStarted" to all users in the room
    io.to(roomName).emit("gameStarted", game);
    if (callback) {
      callback(game);
    }
    // Start the timer for the first player's turn
    if(game.currentPlayer == 1) {
      startTurnTimer(roomName, game.player1Id);
    } else {
      startTurnTimer(roomName, game.player2Id);
    }
  });

  socket.on("fetchPlayingGames", (callback) => {
    if (!util.checkAuthenticatedUser(socket, callback)) {
      return;
    }
    if (callback) {
      callback(util.getGamesPlaying(socket));
    }
  });

  socket.on("play", (playData, callback) => {
    if (!util.checkAuthenticatedUser(socket, callback)) {
      return;
    }
    const roomName = "game_" + playData.gameId;
    // load game state from the game data stored directly on the room object:
    const game = socket.adapter.rooms.get(roomName).game;
    const lastPlayer = game.currentPlayer;
    const playResult = gameEngine.play(game, playData.index, socket.id);
    if (playResult !== true) {
      if (callback) {
        callback(playResult);
      }
      return;
    }
    // notify all users playing the game (in the room) that the game state has changed
    // Also, notify them that the game has ended
    io.to(roomName).emit("gameChanged", game);

    // If the player missed to match the cards, flip them back after 1 second
    // and change the current player to the other player inside flipDownCards method
    if (game.currentPlayer !== lastPlayer) {
      setTimeout(() => {
        gameEngine.flipDownCards(game, lastPlayer);
        io.to(roomName).emit("gameChanged", game);
        const currentPlayerId = game.currentPlayer === 1 ? game.player1Id : game.player2Id;
        resetTurnTimer(roomName, currentPlayerId);
      }, 1000);
    }

    // Game is over
    if (gameEngine.gameEnded(game)) {
      clearTurnTimer(roomName); // Clear the timer
      io.to(roomName).emit("gameEnded", game);
    }
    if (callback) {
      callback(game);
    }
  });

  socket.on("quitGame", (gameId, callback) => {
    if (!util.checkAuthenticatedUser(socket, callback)) {
      return;
    }
    const roomName = "game_" + gameId;
    // load game state from the game data stored directly on the room object:
    const game = socket.adapter.rooms.get(roomName).game;
    const quitResult = gameEngine.quit(game, socket.id);
    if (quitResult !== true) {
      if (callback) {
        callback(quitResult);
      }
      return;
    }
    // notify all users playing the game (in the room) that the game state has changed
    // Also, notify them that the game has been quit and the game has ended
    io.to(roomName).emit("gameChanged", game);
    io.to(roomName).emit("gameQuitted", {
      userQuitId: socket.data.user.id,
      userQuitNickname: socket.data.user.nickname,
      game: game,
    });
    socket.leave(roomName);
    if (callback) {
      callback(game);
    }
  });

  socket.on("closeGame", (gameId, callback) => {
    if (!util.checkAuthenticatedUser(socket, callback)) {
      return;
    }
    const roomName = "game_" + gameId;
    // load game state from the game data stored directly on the room object:
    const socketRoom = socket.adapter.rooms.get(roomName)
    if (!socketRoom){
        return
    }
    const game = socketRoom.game;
    const closeResult = gameEngine.close(game, socket.id);
    if (closeResult !== true) {
      if (callback) {
        callback(closeResult);
      }
      return;
    }
    socket.leave(roomName);
    if (callback) {
      callback(true);
    }
  });

  // Turn timer functions

  function startTurnTimer(roomName, playerId) {
    clearTurnTimer(roomName); // Clear any existing timer
    const timer = setTimeout(() => {
      handleTurnTimeout(roomName, playerId);
    }, 20000); // 20000 milliseconds = 20 seconds
    turnTimers.set(roomName, timer);
  }

  function resetTurnTimer(roomName, playerId) {
    startTurnTimer(roomName, playerId);
  }

  function clearTurnTimer(roomName) {
    if (turnTimers.has(roomName)) {
      clearTimeout(turnTimers.get(roomName));
      turnTimers.delete(roomName);
    }
  }

  function handleTurnTimeout(roomName, playerId) {
    const room = socket.adapter.rooms.get(roomName);
    if (!room || !room.game) {
      return; // Exit if the room or game no longer exists
    }

    const game = room.game;

    if (gameEngine.gameEnded(game)) {
      return;
    }

    if ((game.currentPlayer === 1 && playerId === game.player1Id) || 
        (game.currentPlayer === 2 && playerId === game.player2Id)) {
      // Player timed out, force quit the game
      gameEngine.timeout(game, game.currentPlayer);
      io.to(roomName).emit("gameQuitted", {
        userQuitId: playerId,
        userQuitNickname: socket.data.user.nickname,
        game: game,
      });
    }
    socket.leave(roomName);
    clearTurnTimer(roomName);
  }


});

