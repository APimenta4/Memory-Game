<script setup>

import { useAuthStore } from "@/stores/auth";
import { useGameStore } from "@/stores/game";
import { ref, watch } from "vue";

const props = defineProps({
  cards: {
    type: Array,
    required: true,
  },
  flipCard: {
    type: Function,
    required: true,
  },
})

const emit = defineEmits(['gameStarted'])
const gameStore = useGameStore();
const authStore = useAuthStore();

const gameStarted = ref(false)

watch(
  () => gameStore.reloadRequestMemoryGame,
  () => gameStarted.value = false,
  { deep: true }
)

const flipCardWrapper = (index)=>{
  if ((!(gameStore.board.board_cols===3 && gameStore.board.board_rows===4) && (!authStore.user || authStore.user.brain_coins_balance <= 0))){
    return; 
  }
  if (!gameStarted.value){
    gameStarted.value = true
    emit('gameStarted')
  }
  props.flipCard(index)
}
</script>

<template>
  <div
    class="h-full grid gap-4"
    :style="{ gridTemplateColumns: `repeat(${gameStore.board.board_cols}, 1fr)` }"
  >
    <div
      v-for="(card, index) in cards"
      :key="index"
      class="bg-gray-200 rounded-lg flex justify-center items-center cursor-pointer shadow-lg transition-transform transform hover:scale-105"
      :class="{
        'w-16 h-24': gameStore.board.board_cols===6,
        'w-20 h-28': gameStore.board.board_cols!==6,
        'bg-white': card.isFlipped || card.isMatched,
        'pointer-events-none': card.isMatched,
      }"
      @click="flipCardWrapper(index)"
    >
      <span
        v-if="card.isFlipped || card.isMatched"
        class="text-4xl font-semibold text-gray-900 select-none"
      >
        {{ card.value }}
      </span>
    </div>
  </div>
</template>
