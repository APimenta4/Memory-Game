<script setup>
import { onMounted, watch } from 'vue';
import { useMemoryGame } from '../game/memoryGame.js';
import { useGamesStore } from '@/stores/games';
import { useBoardStore } from '@/stores/board';
import { useAuthStore } from '@/stores/auth.js';

const storeGames = useGamesStore();
const storeBoard = useBoardStore();
const storeAuth = useAuthStore();

const { cards, flipCard } = useMemoryGame(storeGames.board);

storeGames.board = storeBoard.boards.find(board => board.id === storeGames._game.board_id)

onMounted(() => {
});


const flipCardWrapper = (index)=>{
  storeGames.play(storeGames._game, index)
  flipCard(index)
}
// watch _game
//watch(() => storeGames._game, (newGame) => {
//  if (newGame.isFinished) {
//    storeGames.finishGame(newGame)
//  }
//})

</script>

<template>
  <div
    class="h-full grid gap-4"
    :style="{ gridTemplateColumns: `repeat(${storeGames.board.board_cols}, 1fr)` }"
  >
    <div
      v-for="(card, index) in storeGames._game.cards"
      :key="index"
      class="bg-gray-200 rounded-lg flex justify-center items-center cursor-pointer shadow-lg transition-transform transform hover:scale-105"
      :class="{
        'w-16 h-24': storeGames.board.board_cols===6,
        'w-20 h-28': storeGames.board.board_cols!==6,
        'bg-white': card.isFlipped || card.isMatched,
        'pointer-events-none': card.isMatched,
      }"
      @click="flipCardWrapper(index)"
    >
      <span
        v-if="card.isFlipped || card.isMatched"
        class="text-xl font-semibold text-gray-900"
      >
        {{ card.value }}
      </span>
    </div>
  </div>
</template>