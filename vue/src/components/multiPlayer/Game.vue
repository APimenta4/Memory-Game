<script setup>
import { useGamesStore } from '@/stores/games'
import MultiplayerStatusCard from '@/components/multiPlayer/MultiplayerStatusCard.vue'
import MultiplayerStatistics from '../MultiplayerStatistics.vue'
import { ref, onMounted, onUnmounted, watch } from 'vue'
import MultiPlayerGame from './MultiPlayerGame.vue'
import JSConfetti from 'js-confetti'

const jsConfetti = ref(null)

onMounted(() => {
  jsConfetti.value = new JSConfetti({ canvasId: 'confetti' })
})

const storeGames = useGamesStore()

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

watch(()=>storeGames.gameStatus, (newValue) => {
  if (newValue === 'You win') {
    jsConfetti.value
    .addConfetti({
      emojis: ['🏆', '✅', '🧠', '💪', '💲', '💲']
    })
    .then(() => {
      jsConfetti.value.addConfetti()
    })
    
  }else if (newValue === 'You lose') {
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
