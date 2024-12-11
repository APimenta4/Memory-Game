import { ref, computed, inject } from 'vue'
import { defineStore } from 'pinia'
import axios from 'axios'
import { useErrorStore } from '@/stores/error'
import { useAuthStore } from '@/stores/auth'
import { useGameStore } from '@/stores/game'

export const useLobbyStore = defineStore('lobby', () => {
  const storeAuth = useAuthStore()
  const storeError = useErrorStore()
  const storeGame = useGameStore()
  const socket = inject('socket')

  const games = ref([])

  const totalGames = computed(() => games.value.length)

  const webSocketServerResponseHasError = (response) => {
    if (response.errorCode) {
      storeError.setErrorMessages(response.errorMessage, [], response.errorCode)
      return true
    }
    return false
  }

  // when the lobby changes on the server, it is updated on the client
  socket.on('lobbyChanged', (lobbyGames) => {
    games.value = lobbyGames
  })

  // fetch lobby games from the Websocket server
  const fetchGames = () => {
    storeError.resetMessages()
    socket.emit('fetchGames', (response) => {
      if (webSocketServerResponseHasError(response)) {
        return
      }
      games.value = response
    })
  }

  // add a game to the lobby
  const addGame = async (chosenBoardId) => {
    storeError.resetMessages()
    const gameData = await storeGame.insertGame({
      type: 'M',
      status: 'PE',
      board_id: chosenBoardId
    })
    socket.emit('addGame', gameData.id, async (response) => {
      if (webSocketServerResponseHasError(response)) {
        return
      }
    })
  }

  // remove a game from the lobby
  const removeGame = (id) => {
    storeError.resetMessages()
    socket.emit('removeGame', id, async (response) => {
      if (webSocketServerResponseHasError(response)) {
        console.log('Error removing game')
        return
      }
      // TODO soft delete da api? não sei se é delete ou interrupted, mas fiz delete
      try {
        await axios.delete(`games/${id}`)
        return true
      } catch (e) {
        storeError.setErrorMessages(
          e.response.data.message,
          e.response.data.errors,
          e.response.status,
          'Error updating game!'
        )
        return false
      }
    })
  }

  // join a game of the lobby
  const joinGame = (id) => {
    storeError.resetMessages()
    socket.emit('joinGame', id, async (response) => {
      // callback executed after the join is complete
      if (webSocketServerResponseHasError(response)) {
        return
      }
      const APIresponse = await axios.post('games', {
        // TODO post
        player1_id: response.player1.id,
        player2_id: response.player2.id
      })
      const newGameOnDB = APIresponse.data.data
      newGameOnDB.player1SocketId = response.player1SocketId
      newGameOnDB.player2SocketId = response.player2SocketId
      // After adding the game to the DB emit a message to the server to start the game
      socket.emit('startGame', newGameOnDB, (startedGame) => {
        console.log('Game has started', startedGame)
      })
    })
  }

  // Whether the current user can remove a specific game from the lobby
  const canRemoveGame = (game) => {
    return game.creator_id === storeAuth.userId
  }

  // Whether the current user can join a specific game from the lobby
  const canJoinGame = (game) => {
    return storeAuth.user && game.player1.id !== storeAuth.userId
  }

  return {
    games,
    totalGames,
    fetchGames,
    addGame,
    joinGame,
    canJoinGame,
    removeGame,
    canRemoveGame
  }
})
