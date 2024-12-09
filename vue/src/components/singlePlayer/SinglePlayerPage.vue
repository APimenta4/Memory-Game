<script setup>
import { onMounted, ref } from 'vue';
import { useBoardStore } from '@/stores/board'
import { useAuthStore } from '@/stores/auth'
import { useErrorStore } from '@/stores/error'
import BoardList from './BoardList.vue';

const storeBoard = useBoardStore()
const storeError = useErrorStore()

onMounted(() => {
    storeError.resetMessages()
})
</script>


<template>
  <div class="pt-4">      
    <h1 class="text-3xl font-bold text-gray-900 sm:text-4xl">
      Boards
    </h1>
      <div class="flex space-x-4 pb-4">
        <BoardList v-show="storeBoard.totalBoards > 0"
          :boards="storeBoard.boards"></BoardList>
      </div>
    <div v-show="storeBoard.totalBoards === 0" class="text-gray-500 text-center">
      No boards available.
    </div>
  </div>
</template>