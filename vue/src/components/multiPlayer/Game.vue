<script setup>
import { onMounted, onUnmounted, watch } from "vue";
import { useMemoryGame } from "./memoryGame.js";
import { useGameStore } from "@/stores/game";
import { useAuthStore } from "@/stores/auth.js";
import router from "@/router";


const storeGame = useGameStore();
const storeAuth = useAuthStore();

   

const { cards, isGameOver, flipCard, resetGame,  totalTurns, getTotalTime} = useMemoryGame(storeGame.board);


onMounted(()=>{
  console.log("Current board:", storeGame.board.id);
  if (!storeGame.board.id){
    router.push("/singleplayer");
  }
  console.log("storeAuth.user = " + storeAuth.user)

  if(storeAuth.user){
    console.log("insert game")
    storeGame.insertGame({
      type:"S",
      status:"PL",
      board_id:storeGame.board.id
    })
  }
})

watch(
  isGameOver,
  async (newValue) => {
    if (newValue && storeAuth.user){
      await storeGame.updateGame({
        status: "E",
        total_time: getTotalTime(),//change to actual value
        total_turns_winner: totalTurns.value,//change to actual value
      })
    }
  }
)

onUnmounted(()=>{
  console.log("isGameOver.value = " + isGameOver.value)
  console.log("storeAuth.user = " + storeAuth.user)
  if (!isGameOver.value && storeAuth.user){
    console.log("in")
    storeGame.updateGame({status: "I"})
  }
})
</script>

<template>
  <div class="flex flex-col items-center">
    <h1 class="text-2xl font-bold mt-6 mb-4">Memory Game</h1>
    <div class="grid gap-4" :style="{ gridTemplateColumns: `repeat(${storeGame.board.board_cols}, 1fr)` }">
      <div
        v-for="(card, index) in cards"
        :key="index"
        class="w-20 h-28 bg-gray-200 rounded-lg flex justify-center items-center cursor-pointer shadow-lg transition-transform transform hover:scale-105"
        :class="{
          'bg-white': card.isFlipped || card.isMatched,
          'pointer-events-none': card.isMatched,
        }"
        @click="flipCard(index)"
      >
        <span
          v-if="card.isFlipped || card.isMatched"
          class="text-xl font-semibold text-gray-900"
        >
          {{ card.value }}
        </span>
      </div>
    </div>
    <div v-if="isGameOver" class="text-center mt-6">
      <p class="text-green-600 font-bold">🎉 You won! Great memory! 🎉</p>
      <button
        @click="resetGame"
        class="mt-4 bg-blue-500 text-white px-6 py-2 rounded shadow hover:bg-blue-600 transition"
      >
        Restart
      </button>
    </div>
  </div>
</template>