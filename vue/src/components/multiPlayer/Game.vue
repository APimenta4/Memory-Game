<script setup>
import { useGamesStore } from '@/stores/games'
import MultiplayerStatusCard from '@/components/multiPlayer/MultiplayerStatusCard.vue'
import MultiplayerStatistics from '../MultiplayerStatistics.vue'
import { ref, onMounted, onUnmounted, watch } from 'vue'
import JSConfetti from 'js-confetti'

const jsConfetti = ref(null)

onMounted(() => {
  jsConfetti.value = new JSConfetti({ canvasId: 'confetti' })
})

const celebrate = () => {
  
}

const storeGames = useGamesStore()

const flipCardWrapper = (index) => {
  storeGames.play(storeGames._game, index)
}

onUnmounted(async () => {
  if (storeGames._game.gameStatus === 0) {
    await storeGames.quit(storeGames._game)
  }
})

watch(()=>storeGames.gameStatus, (newValue) => {
  if (newValue === 'You win') {
    jsConfetti.value
    .addConfetti({
      emojis: ['🏆', '✅', '🧠', '💪', '💲', '💲', '+500 AURA']
    })
    .then(() => {
      jsConfetti.value.addConfetti()
    })
    
  }else if (newValue === 'You lose') {
    jsConfetti.value
    .addConfetti({
     emojis: ['❓', '💩', '🤡', '❓', '💩', '🤡', '-500 AURA']
    })
  }
}, { deep: true })
</script>

<template>
  <canvas id="confetti"></canvas>
  <div class="flex flex-col lg:flex-row justify-center space-x-6 md:space-x-0">
    <div class="flex flex-col justify-start items-center max-h-full mt-5">
      <MultiplayerStatusCard />
    </div>

    <div class="flex flex-col items-center w-full md:w-3/4">
      <h1 class="text-2xl font-bold mt-6 mb-4">
        <span
          :class="{
            'text-green-500': storeGames.myPlayerNumber === storeGames._game.currentPlayer
          }"
        >
          {{
            storeGames.myPlayerNumber === storeGames._game.currentPlayer
              ? 'Your turn'
              : storeGames._game['player' + storeGames.opponentPlayerNumber + 'Nickname'] +
                "'s turn"
          }}
        </span>
      </h1>
      <div
        class="h-full grid gap-4 justify-center"
        :style="{ gridTemplateColumns: `repeat(${storeGames._game.cols}, 1fr)` }"
      >
        <div
          v-for="(card, index) in storeGames._game.cards"
          :key="index"
          class="bg-gray-200 rounded-lg flex justify-center items-center cursor-pointer shadow-lg transition-transform transform hover:scale-105"
          :class="{
            'w-16 h-24': storeGames.board.board_cols === 6,
            'w-20 h-28': storeGames.board.board_cols !== 6,
            'bg-white': card.isFlipped || card.isMatched,
            'pointer-events-none': card.isMatched
          }"
          @click="flipCardWrapper(index)"
        >
          <span v-if="card.isFlipped || card.isMatched" class="text-xl font-semibold text-gray-900">
            {{ card.value }}
          </span>
        </div>
      </div>
    </div>

    <div class="flex flex-col justify-start items-center max-h-full mt-5">
      <MultiplayerStatistics />
    </div>
  </div>
</template>
