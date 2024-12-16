<script setup>
import Board from './BoardSelection.vue'

const props = defineProps({
  boards: {
    type: Array,
    required: true,
  },
})
</script>

<template>
  <div class="flex flex-wrap space-x-4 pb-4 justify-center items-center">
    <Board v-for="board in boards" :key="board.id" :board="board" />
  </div>
</template>