import { ref, computed, inject } from 'vue'
import { defineStore } from 'pinia'
import axios from 'axios'
import { useErrorStore } from '@/stores/error'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/components/ui/toast/use-toast'
import { useRouter } from 'vue-router'
import { useGameStore } from '@/stores/game'
import { useMemoryGame } from '@/components/game/memoryGame'
import { extendTailwindMerge } from 'tailwind-merge'
import { end } from '@popperjs/core'

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
    cards,
    isGameOver,
    flipCard,
    resetGame,
    totalTurns,
    pairsFound,
    getCurrentTime,
    getTotalTime
  } = useMemoryGame(storeGame.board)

  const games = ref([])

  const _game = ref({})
  const board = ref({})

  const totalGames = computed(() => games.value.length)

  // Use this function to update the game object in the games array
  const updateGame = (game) => {
    //const gameIndex = games.value.findIndex((g) => g.id === game.id)
    //if (gameIndex !== -1) {
    //    games.value[gameIndex] = { ...game } // shallow copy
    //}
    _game.value = game
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
        _game.value = response
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
    }
    _game.value = game
    fetchPlayingGames()
    router.push({
      path: '/multiplayer/game'
    })
    startTime.value = Date.now()
    
  })

  socket.on('gameEnded', async (game) => {
    updateGame(game)
    endTime.value = Date.now()
    // Player that created the game is responsible for updating on the database
    if (playerNumberOfCurrentUser(game) === 1) {
      await storeGame.updateGame({
        status: 'E',
        winner_user_id: game[`player${game.gameStatus}Id`],
        total_time: getTotalTime(),
        total_turns_winner: game[`player${playerNumberOfCurrentUser(game)}Turns`]
      })
    }
  })

  socket.on('gameChanged', (game) => {
    updateGame(game)
  })

  socket.on('gameQuitted', async (payload) => {
    if (payload.userQuit.id != storeAuth.userId) {
      toast({
        title: 'Game Quit',
        description: `${payload.userQuit.name} has quitted game #${payload.game.id}, giving you the win!`
      })
    }
    updateGame(payload.game)
    endTime.value = Date.now()
    await storeGame.updateGame({
      status: 'E',
      winner_user_id: storeAuth.user.id,
      total_time: getTotalTime(),
      total_turns_winner: payload.game[`player${playerNumberOfCurrentUser(payload.game)}Turns`]
    })
  })

  socket.on('gameInterrupted', async (game) => {
    console.log('Game interrupted', JSON.stringify(game))
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
    _game,
    board,
    totalGames,
    playerNumberOfCurrentUser,
    fetchPlayingGames,
    play,
    quit,
    close
  }
})
