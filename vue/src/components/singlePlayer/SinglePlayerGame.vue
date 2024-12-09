<script setup>
import { useMemoryGame } from "./memoryGame.js";
import { useGameStore } from "@/stores/game";

const storeGame = useGameStore();

const { cards, isGameOver, flipCard, resetGame } = useMemoryGame(storeGame.board);
</script>

<template>
  <div class="flex flex-col items-center">
    <h1 class="text-2xl font-bold mt-6 mb-4">Memory Game</h1>
    <div class="grid gap-4" :style="{ gridTemplateColumns: `repeat(${storeGame.board.board_cols}, 1fr)` }">
      <!-- Loop through cards and display each -->
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
    <!-- Game Over Section -->
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