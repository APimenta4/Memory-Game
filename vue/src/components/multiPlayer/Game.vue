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

onMounted(() => {
  jsConfetti.value = new JSConfetti({ canvasId: 'confetti' })
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
    <div class="flex flex-col justify-start items-center max-h-full mt-5">
      <MultiplayerStatusCard />
    </div>

    <div class="flex flex-col items-center w-full md:w-3/4">
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
        </span>
      </h1>
      <MultiPlayerGame :storeGames="storeGames" :flipCardWrapper="flipCardWrapper" />
    </div>

    <div class="flex flex-col justify-start items-center max-h-full mt-5">
      <MultiplayerStatistics />
    </div>
  </div>
  <canvas id="confetti"></canvas>
</template>
