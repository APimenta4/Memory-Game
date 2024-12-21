import { ref, computed, inject } from 'vue'
import { defineStore } from 'pinia'
import { useErrorStore } from '@/stores/error'
import { useAuthStore } from '@/stores/auth'
import { useGameStore } from '@/stores/game'
import { useBoardStore } from '@/stores/board'
import { toast } from '@/components/ui/toast'

export const useLobbyStore = defineStore('lobby', () => {
  const storeAuth = useAuthStore()
  const storeError = useErrorStore()
  const storeGame = useGameStore()
  const storeBoard = useBoardStore()
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
    if(gameData == false){
      return
    }
    storeAuth.updateBalance()
    const board = storeBoard.boards.find((board) => board.id === chosenBoardId)
    const cols = board.board_cols
    const rows = board.board_rows
    socket.emit('addGame', gameData.id, cols, rows, async (response) => {
      if (webSocketServerResponseHasError(response)) {
        return
      }
    })
  }

  // remove a game from the lobby
  const removeGame = async (id) => {
    storeError.resetMessages()
    try {
      const response = await storeGame.updateGameWithId(id, { status: 'I' })
      if(response){
        throw response
      }
      socket.emit('removeGame', id, async (response) => {
        if (webSocketServerResponseHasError(response)) {
          return
        }
      })
    } catch (e) {
      console.log(e)
      storeError.setErrorMessages(
        e.response.message,
        e.response.errors,
        e.response.status,
        'Error removing game!'
      )
      return
    }
  }

  // join a game of the lobby
  const joinGame = async (id) => {
    storeError.resetMessages()
    try {
      const response = await storeGame.updateGameWithId(id, { status: 'PL' })
      if (response?.status == 422) {
        toast({
          title: 'Could not join the Multiplayer Game!',
          description: response.data.message,
          variant: 'destructive'
        })
        return
      }
      storeAuth.updateBalance()
      socket.emit('joinGame', id, async (response) => {
        // callback executed after the join is complete
        if (webSocketServerResponseHasError(response)) {
          return
        }

        // After updating the game on the DB emit a message to the server to start the game
        socket.emit('startGame', response, () => {})
      })
    } catch (e) {
      console.log(e)
      storeError.setErrorMessages(
        e.response.data.message,
        e.response.data.errors,
        e.response.status,
        'Error joining game!'
      )
      return
    }
  }

  // Whether the current user can remove a specific game from the lobby
  const canRemoveGame = (game) => {
    return game.player1Id === storeAuth.user.id
  }

  // Whether the current user can join a specific game from the lobby
  const canJoinGame = (game) => {
    return game.player1Id !== storeAuth.user.id
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
