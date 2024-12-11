<script setup>
import Board from './Board.vue'

const props = defineProps({
  boards: {
    type: Array,
    required: true,
  },
})
</script>

<template>
    <div  class="flex space-x-4 pb-4">
      <Board v-for="board in boards" :key="board.id"
        :board="board" 
        />
    </div>
</template>