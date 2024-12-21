import { ref, computed, inject } from 'vue'
import { defineStore } from 'pinia'
import { useErrorStore } from '@/stores/error'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/components/ui/toast/use-toast'
import { useRouter } from 'vue-router'
import { useGameStore } from '@/stores/game'
import { useMemoryGame } from '@/components/game/memoryGame'


export const useGamesStore = defineStore('games', () => {
  const storeAuth = useAuthStore()
  const storeError = useErrorStore()
  const { toast } = useToast()
  const router = useRouter()
  const socket = inject('socket')
  const storeGame = useGameStore()
  const {
    startTime,
    endTime,
    getCurrentTime,
    getTotalTime
  } = useMemoryGame(storeGame.board)

  const games = ref([])
  const currentGame = ref({})
  const myPlayerNumber = ref(null)
  const opponentPlayerNumber = ref(null)
  const gameStatus = ref(null)
  const board = ref({})
  const isClosed = ref(false)

  const totalGames = computed(() => games.value.length)

  // Update the local copy of the game
  const updateGame = (game) => {
    currentGame.value = game
  }

  const playerNumberOfCurrentUser = (game) => {
    if (game.player1Id === storeAuth.user.id) {
      return 1
    }
    if (game.player2Id === storeAuth.user.id) {
      return 2
    }
    return null
  }

  const webSocketServerResponseHasError = (response) => {
    if (response.errorCode) {
      storeError.setErrorMessages(response.errorMessage, [], response.errorCode)
      return true
    }
    return false
  }

  const removeGameFromList = (game) => {
    const gameIndex = games.value.findIndex((g) => g.id === game.id)
    if (gameIndex >= 0) {
      games.value.splice(gameIndex, 1)
    }
  }

  // fetch playing games from the Websocket server
  const fetchPlayingGames = () => {
    storeError.resetMessages()
    socket.emit('fetchPlayingGames', (response) => {
      if (webSocketServerResponseHasError(response)) {
        return
      }
      games.value = response
    })
  }

  const play = (game, idx) => {
    storeError.resetMessages()
    socket.emit(
      'play',
      {
        index: idx,
        gameId: game.id
      },
      (response) => {
        if (webSocketServerResponseHasError(response)) {
          return
        }
        updateGame(response)
        currentGame.value = response
      }
    )
  }

  const quit = (game) => {
    storeError.resetMessages()
    socket.emit('quitGame', game.id, (response) => {
      if (webSocketServerResponseHasError(response)) {
        return
      }
      removeGameFromList(game)
    })
  }

  const close = (game) => {
    storeError.resetMessages()
    socket.emit('closeGame', game.id, (response) => {
      if (webSocketServerResponseHasError(response)) {
        return
      }
      removeGameFromList(game)
    })
  }

  socket.on('gameStarted', async (game) => {
    if (game.player1Id === storeAuth.user.id) {
      toast({
        title: 'Game Started',
        description: `Game #${game.id} has started!`
      })
      myPlayerNumber.value = 1
      opponentPlayerNumber.value = 2
    } else {
      myPlayerNumber.value = 2
      opponentPlayerNumber.value = 1
    }
    currentGame.value = game
    fetchPlayingGames()
    router.push({
      path: '/multiplayer/game'
    })
    startTime.value = Date.now()  
    gameStatus.value = "Playing"
  })

  socket.on('gameEnded', async (game) => {
    updateGame(game)
    endTime.value = Date.now()
    if(game.gameStatus > 0){
      if(playerNumberOfCurrentUser(game)==game.gameStatus){
        gameStatus.value = "You win"
      }else{
        gameStatus.value = "You lose"
      }
    } 

    // Player that created the game is responsible for updating on the database
    if (playerNumberOfCurrentUser(game) === 1) {
      await storeGame.updateGame({
        status: 'E',
        winner_user_id: game[`player${game.gameStatus}Id`],
        total_time: getTotalTime(),
        total_turns_winner: game[`player${playerNumberOfCurrentUser(game)}Turns`]
      })
      storeAuth.updateBalance()
    }
  })

  socket.on('gameChanged', (game) => {
    updateGame(game)
  })

  socket.on('gameQuitted', async (payload) => {
    updateGame(payload.game)
    if(payload.userQuitId != storeAuth.user.id) {
      toast({
        title: 'Game Quit',
        description: `${payload.userQuitNickname} has quitted game #${payload.game.id}, giving you the win!`
      })
      endTime.value = Date.now()
      await storeGame.updateGame({
        status: 'E',
        winner_user_id: storeAuth.user.id,
        total_time: getTotalTime(),
        total_turns_winner: payload.game[`player${playerNumberOfCurrentUser(payload.game)}Turns`]
      })
      gameStatus.value = 'Opponent quit'
    }else{
      gameStatus.value = 'You quit'
    }
  })

  socket.on('gameInterrupted', async (game) => {
    updateGame(game)
    toast({
      title: 'Game Interruption',
      description: `Game #${game.id} was interrupted because your opponent has gone offline!`,
      variant: 'destructive'
    })
    await storeGame.updateGame({ status: 'I' })
  })

  return {
    games,
    currentGame,
    gameStatus,
    myPlayerNumber,
    opponentPlayerNumber,
    board,
    totalGames,
    isClosed,
    getCurrentTime,
    playerNumberOfCurrentUser,
    fetchPlayingGames,
    play,
    quit,
    close
  }
})
