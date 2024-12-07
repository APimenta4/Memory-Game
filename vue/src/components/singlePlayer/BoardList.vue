<script setup>
import Board from './Board.vue'

import { ScrollArea, ScrollBar } from '@/components/ui/scroll-area';

const props = defineProps({
  boards: {
    type: Array,
    required: true,
  },
})
</script>

<template>
  <ScrollArea class="border rounded-md whitespace-nowrap m-3 p-3">
    <div  class="flex space-x-4 pb-4">
      <Board v-for="board in boards":key="board.id"
        :board="board" 
        />
    </div>
    <ScrollBar orientation="horizontal" />
  </ScrollArea>  
</template>