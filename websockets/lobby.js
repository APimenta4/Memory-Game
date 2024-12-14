exports.createLobby = () => { 
    const games = new Map()

    const addGame = (userId, userNickname, socketId, gameDataId, cols, rows) => {
        const game = {
            id: gameDataId,
            player1Id: userId,
            player1Nickname: userNickname,
            player1SocketId: socketId,
            cols: cols,
            rows: rows,
            lobby_timestamp: Date.now(),
        }
        games.set(gameDataId, game)
        return game
    }
    
    const removeGame = (id) => {
        games.delete(id)
        return games
    }

    const existsGame = (id) => {
        return games.has(id)
    }

    const getGame = (id) => {
        return games.get(id)
    }

    const getGames = () => {
        return [...games.values()]
    }

    const leaveLobby = (socketId) => {
        const gamesToDelete = [...games.values()].filter(game => game.player1SocketId == socketId)
        gamesToDelete.forEach(game => {
            games.delete(game.id)
        })
        return getGames()
    }

    return {
        getGames,
        getGame,
        addGame,
        removeGame,
        existsGame,
        leaveLobby
    }
}