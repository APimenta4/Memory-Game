<script setup>
import {
  onMounted,
  onUnmounted,
  onBeforeUnmount,
  watch,
  ref,
  h,
  inject,
  onBeforeMount,
  computed
} from 'vue'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger
} from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { useMemoryGame } from '@/components/game/memoryGame'
import { useGameStore } from '@/stores/game'
import { useAuthStore } from '@/stores/auth'

import { useToast } from '@/components/ui/toast/use-toast'
import { ToastAction } from '@/components/ui/toast'

import Top5Card from '@/components/game/Top5Card.vue'
import GameStatusCard from '@/components/game/GameStatusCard.vue'
import MemoryGame from '@/components/game/MemoryGame.vue'

import router from '@/router'

const socket = inject('socket')
import JSConfetti from 'js-confetti'

const jsConfetti = ref(null)

const { toast } = useToast()

const storeGame = useGameStore()
const storeAuth = useAuthStore()
const {
  cards,
  isGameOver,
  flipCard,
  resetGame,
  totalTurns,
  pairsFound,
  getCurrentTime,
  getTotalTime
} = useMemoryGame(storeGame.board)

const currentTime = ref('')
const updateCurrentTime = () => {
  currentTime.value = getCurrentTime()
}
const updateTimeInterval = setInterval(updateCurrentTime, 100)
let gameStarted = false

const createGame = async () => {
  gameStarted = true
  if (storeAuth.user) {
    await storeGame.insertGame({
      type: 'S',
      status: 'PL',
      board_id: storeGame.board.id
    })
    storeAuth.updateBalance()
  }
}

const restartGame = async () => {
  gameStarted = false
  storeGame.reloadRequestMemoryGame = !storeGame.reloadRequestMemoryGame

  if (storeGame.game.id && !isGameOver.value && storeAuth.user) {
    await storeGame.updateGame({ status: 'I' })
  }
  resetGame()
  createGame()
}

watch(isGameOver, async (newValue) => {
  if (newValue) {
    if (storeAuth.user) {
      await storeGame.updateGame({
        status: 'E',
        total_time: getTotalTime(),
        total_turns_winner: totalTurns.value
      })
      socket.emit('notification_alert', storeAuth.user.id)
    }
    jsConfetti.value
      .addConfetti({
        emojis: ['🏆', '✅', '🧠', '💪', '🧠']
      })
      .then(() => {
        jsConfetti.value.addConfetti()
      })
    storeGame.reloadRequestTop5 = !storeGame.reloadRequestTop5
    storeGame.reloadRequestMemoryGame = !storeGame.reloadRequestMemoryGame
  }
})
onBeforeMount(() => {
  if (Object.keys(storeGame.board).length === 0) {
    router.push('/singleplayer')
    return
  }
})
onMounted(() => {
  jsConfetti.value = new JSConfetti({ canvasId: 'confetti' })

  if (!storeAuth.user) {
    toast({
      title: 'Log in to Enhance Your Experience',
      description:
        "Log in to ensure your records appear on the Scoreboard and stay saved. It's quick and easy!",
      action: h(
        ToastAction,
        {
          altText: 'Log in to access additional features',
          onclick: () => {
            router.push('/login')
          }
        },
        {
          default: () => 'Log In'
        }
      )
    })
  }
  createGame()
})

onBeforeUnmount(() => clearInterval(updateTimeInterval))
onUnmounted(async () => {
  if (storeAuth.user && gameStarted && !isGameOver.value) {
    await storeGame.updateGame({ status: 'I' })
  }
})
</script>
<template>
  <div class="flex flex-col lg:flex-row justify-center space-x-6 md:space-x-0">
    <div class="flex flex-col md:flex-row md:justify-center w-full">
      <Dialog>
        <GameStatusCard
          class="mt-5 mr-5 w-full md:w-1/4 md:order-1 order-2"
          :is-game-over="isGameOver"
          :pairs-found="pairsFound"
          :time="currentTime"
          :total-pairs="cards.length / 2"
          :turns="totalTurns"
        />
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Interrupt game</DialogTitle>
            <DialogDescription> Would you like to restart the game? </DialogDescription>
          </DialogHeader>
          <DialogFooter>
            <DialogTrigger asChild>
              <Button @click="restartGame" variant="destructive">Restart Game</Button>
            </DialogTrigger>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      <div class="flex flex-col items-center w-full md:w-3/4 md:order-2 order-1">
        <h1 class="text-2xl font-bold mt-6 mb-4">Memory Game</h1>
        <MemoryGame :cards="cards" :flipCard="flipCard" />
      </div>
    </div>
    <Top5Card class="mt-5 mx-5" :board="storeGame.board" />
  </div>
  <canvas id="confetti"></canvas>
</template>
