<script setup>

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
  }
})

const emit = defineEmits(['gameStarted'])
const storeGame = useGameStore();

const gameStarted = ref(false)

watch(
  () => storeGame.reloadRequestMemoryGame,
  ()=>gameStarted.value = false,
  { deep: true }
)

// testar function
const flipCardWrapper = (index)=>{
  if (!gameStarted.value){
    emit('gameStarted')
  }
  gameStarted.value = true
  props.flipCard(index)
}
</script>

<template>
  <div
    class="h-full grid gap-4"
    :style="{ gridTemplateColumns: `repeat(${storeGame.board.board_cols}, 1fr)` }"
  >
    <div
      v-for="(card, index) in cards"
      :key="index"
      class="bg-gray-200 rounded-lg flex justify-center items-center cursor-pointer shadow-lg transition-transform transform hover:scale-105"
      :class="{
        'w-16 h-24': storeGame.board.board_cols===6,
        'w-20 h-28': storeGame.board.board_cols!==6,
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
