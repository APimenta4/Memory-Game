<script setup>
import { useGamesStore } from '@/stores/games'
import MultiplayerStatusCard from '@/components/multiPlayer/MultiplayerStatusCard.vue'
import MultiplayerStatistics from '../MultiplayerStatistics.vue'
import { ref, onMounted, onBeforeMount, onUnmounted, watch, inject } from 'vue'
import MultiPlayerGame from './MultiPlayerGame.vue'
import JSConfetti from 'js-confetti'
import { useAuthStore } from '@/stores/auth'
import router from '@/router'


const socket = inject('socket')
const jsConfetti = ref(null)
const storeGames = useGamesStore()
const storeAuth = useAuthStore()

const timer = ref(20.0)
let startTime = null
let timerInterval = null

const refreshTimer = () => {
  timer.value = 20.0
  if (timerInterval) clearInterval(timerInterval)
  startTime = performance.now()
  timerInterval = setInterval(() => {
    const elapsed = (performance.now() - startTime) / 1000
    timer.value = Math.max(20.0 - elapsed, 0)
    if (timer.value <= 0) {
      clearInterval(timerInterval)
    }
  }, 100)
}

watch(() => storeGames.currentGame, (newValue) => {
  console.log(newValue)
if (newValue.gameStatus == 0 && newValue.currentPlayer != 0 && newValue.cards.filter(card => card.isFlipped).length % 2 === 0) {
  refreshTimer()
} else {
  if(newValue.currentPlayer == 0){
    clearInterval(timerInterval)
  }
}
})

onMounted(() => {
  jsConfetti.value = new JSConfetti({ canvasId: 'confetti' })
  refreshTimer()
})

const flipCardWrapper = (index) => {
  storeGames.play(storeGames.currentGame, index)
}

onUnmounted(async () => {
  if (storeGames.currentGame.gameStatus === 0) {
    await storeGames.quit(storeGames.currentGame)
  }
  if (storeGames.isClosed){
    storeGames.close(storeGames.currentGame)
  }
})

onBeforeMount(() => {
  if(Object.keys(storeGames.currentGame).length === 0){
    router.push({ name: 'multiplayer' })
  }
})

watch(()=>storeGames.gameStatus, (newValue) => {
  if (newValue === 'You win' || newValue === 'Opponent quit') {
    jsConfetti.value
    .addConfetti({
      emojis: ['🏆','✅','🧠','💪','🧠']
    })
    .then(() => {
      jsConfetti.value.addConfetti()
    })
    socket.emit('notification_alert',storeAuth.user.id)
  }else if (newValue === 'You lose' || newValue === 'You quit') {
    jsConfetti.value
    .addConfetti({
     emojis: ['😢', '💀', '🤡', '❌', '⁉️']
    })
  }
}, { deep: true })
</script>

<template>
    <div class="flex flex-col lg:flex-row justify-center space-x-6 md:space-x-0">
      <div class="flex flex-col md:flex-row md:justify-center w-full">
          <MultiplayerStatusCard class="md:order-1 order-2"/>

        <div class="flex flex-col items-center w-full md:w-3/4 md:order-2 order-1">
          <h1 class="text-2xl font-bold mt-6 mb-4">
            <span
              :class="{
                'text-green-500': storeGames.myPlayerNumber === storeGames.currentGame.currentPlayer
              }"
            >
              {{
              storeGames.myPlayerNumber === storeGames.currentGame.currentPlayer
                ? 'Your turn'
                : storeGames.currentGame['player' + storeGames.opponentPlayerNumber + 'Nickname'] +
                "'s turn"
              }} 
              <br/>
              {{ timer.toFixed(1) }}s
            </span>
          </h1>

          <MultiPlayerGame :storeGames="storeGames" :flipCardWrapper="flipCardWrapper" />
        </div>
    </div>
    <MultiplayerStatistics class="mt-5 mx-5"/>
  </div>
  <canvas id="confetti"></canvas>
</template>
