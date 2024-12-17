<script setup>
import { onMounted, onUnmounted, onBeforeUnmount, watch, ref, h, inject, onBeforeMount } from 'vue'
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
  console.log('Current board:', storeGame.board.id)
  console.log('storeAuth.user = ' + storeAuth.user)

  if (storeAuth.user) {
    console.log('insert game')
    await storeGame.insertGame({
      type: 'S',
      status: 'PL',
      board_id: storeGame.board.id
    })
  }
}

const restartGame = async () => {
  gameStarted = false
  if (storeGame.game.id && !isGameOver.value && storeAuth.user) {
    await storeGame.updateGame({ status: 'I' })
  }
  resetGame()
}

watch(isGameOver, async (newValue) => {
  if(newValue){
    if (storeAuth.user) {
      await storeGame.updateGame({
        status: 'E',
        total_time: getTotalTime(),
        total_turns_winner: totalTurns.value
      })
      socket.emit('notification_alert')
    }
    jsConfetti.value
    .addConfetti({
      emojis:  ['🏆','✅','🧠','💪','🧠']
    })
    .then(() => {
      jsConfetti.value.addConfetti()
    })
    storeGame.reloadRequestTop5 = !storeGame.reloadRequestTop5
    storeGame.reloadRequestMemoryGame = !storeGame.reloadRequestMemoryGame
  }
})
onBeforeMount(()=>{
  if (!storeGame.board.id) {
    router.push('/singleplayer')
    return
  }
})
onMounted(() => {
  if (!storeGame.board.id) {
    router.push('/singleplayer')
    return
  }
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
            router.push('/testers/laravel')
          }
        },
        {
          default: () => 'Log In'
        }
      )
    })
  }
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
    <div class="flex flex-row justify-center w-full">
      <GameStatusCard
        class="mt-5 mr-5 w-1/4"
        :is-game-over="isGameOver"
        :pairs-found="pairsFound"
        :time="currentTime"
        :total-pairs="cards.length / 2"
        :turns="totalTurns"
        @restart="restartGame"
      />
      <div class="flex flex-col items-center w-full md:w-3/4">
        <h1 class="text-2xl font-bold mt-6 mb-4">Memory Game</h1>
        <MemoryGame :cards="cards" :flipCard="flipCard" @gameStarted="createGame" />
      </div>
    </div>
    <Top5Card class="mt-5 mx-5" :board="storeGame.board" />
  </div>
</template>