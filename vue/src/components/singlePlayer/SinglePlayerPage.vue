<script setup>
import { onMounted } from 'vue';
import { useBoardStore } from '@/stores/board'
import { useErrorStore } from '@/stores/error'
import BoardList from './BoardSelectionList.vue'
import { useAuthStore } from '@/stores/auth' ;

const storeBoard = useBoardStore()
const storeError = useErrorStore()
const storeAuth = useAuthStore()  

console.log(storeAuth.user);

onMounted(() => {
    storeError.resetMessages()
})
</script>


<template>
  <div class="flex flex-col items-center pt-4">
    <h1 class="text-3xl font-bold mb-10 text-gray-900 sm:text-4xl">
      Boards
    </h1>
    <div v-show="storeBoard.totalBoards > 0" class="text-gray-500 text-center">
      The brain coin will be used when you flip the first card
    </div>
    <div class="flex justify-center w-full pb-4 items-center">
      <BoardList 
        v-show="storeBoard.totalBoards > 0"
        :boards="storeBoard.boards">
      </BoardList>
    </div>
    <div v-show="storeBoard.totalBoards === 0" class="text-gray-500 text-center">
      No boards available.
    </div>
  </div>
</template>
