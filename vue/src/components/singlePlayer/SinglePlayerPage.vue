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

const boards = ref([
  {id:1,board_size:"3x4"},
  {id:2,board_size:"3x4"},
  {id:3,board_size:"3x4"},
  {id:4,board_size:"3x4"},
  {id:5,board_size:"3x4"},
  {id:6,board_size:"3x4"},
  {id:7,board_size:"3x4"},
  {id:8,board_size:"3x4"},
  {id:9,board_size:"3x4"},
  {id:10,board_size:"3x4"},
  {id:11,board_size:"3x4"},
  {id:12,board_size:"3x4"},
  {id:13,board_size:"3x4"},
  {id:14,board_size:"3x4"},
  {id:15,board_size:"3x4"},
  {id:16,board_size:"3x4"},
  {id:17,board_size:"3x4"},
  {id:18,board_size:"3x4"},
  {id:19,board_size:"3x4"},
  {id:20,board_size:"3x4"},
  {id:21,board_size:"3x4"},
  {id:22,board_size:"3x4"},
])
</script>


<template>
  <div class="pt-4">      
    <h1 class="text-3xl font-bold text-gray-900 sm:text-4xl">
      Boards
    </h1>
      <div class="flex space-x-4 pb-4">
        <BoardList v-show="storeBoard.totalBoards > 0"
          :boards="boards"></BoardList>
          <!-- storeBoard.boards -->
      </div>
    <div v-show="storeBoard.totalBoards === 0" class="text-gray-500 text-center">
      No boards available.
    </div>
  </div>
</template>