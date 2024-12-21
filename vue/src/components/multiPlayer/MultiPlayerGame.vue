<script>
export default {
  props: {
    storeGames: Object,
    flipCardWrapper: Function
  }
};
</script>
<template>
  <div
    class="h-full grid gap-4"
    :style="{ gridTemplateColumns: `repeat(${storeGames.currentGame.cols}, 1fr)` }"
  >
    <div
      v-for="(card, index) in storeGames.currentGame.cards"
      :key="index"
      class="bg-gray-200 rounded-lg flex justify-center items-center cursor-pointer shadow-lg transition-transform transform hover:scale-105"
      :class="{
        'w-16 h-24': storeGames.currentGame.cols === 6,
        'w-20 h-28': storeGames.currentGame.cols !== 6,
        'bg-white': card.isFlipped || card.isMatched,
        'pointer-events-none': card.isMatched
      }"
      @click="flipCardWrapper(index)"
    >
      <span v-if="card.isFlipped || card.isMatched" class="text-4xl font-semibold text-gray-900  select-none">
        {{ card.value }}
      </span>
    </div>
  </div>
</template>
