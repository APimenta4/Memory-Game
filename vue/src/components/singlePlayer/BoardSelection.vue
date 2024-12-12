<script setup>
import { Card, CardTitle } from '@/components/ui/card';
import { useGameStore } from '@/stores/game';
// Data to be fetched
const props = defineProps({
  board: {
    type: Object,
    required: true,
  },
})

const storeGame = useGameStore()

const setBoard = () => storeGame.board = props.board;
</script>

<template>
<RouterLink to="/singleplayer/game" @click="setBoard"
  class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
  active-class="text-blue-600 font-semibold">
  <Card class="w-96 h-64 flex flex-col justify-center items-center p-6 hover:shadow-lg cursor-pointer">
    <CardTitle>
      {{ board.board_cols }}x{{ board.board_rows }}
    </CardTitle>
  </Card>
</RouterLink>
</template>