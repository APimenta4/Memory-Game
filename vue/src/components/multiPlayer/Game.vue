<script setup>
import { onMounted, onBeforeUnmount, watch, ref } from 'vue';
import { useMemoryGame } from '../game/memoryGame.js';
import { useGamesStore } from '@/stores/games';
import { useBoardStore } from '@/stores/board';
import { useAuthStore } from '@/stores/auth.js';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import GameStatusCard from "@/components/game/GameStatusCard.vue";

const storeGames = useGamesStore();
const storeBoard = useBoardStore();
const storeAuth = useAuthStore();

const flipCardWrapper = (index) => {
  storeGames.play(storeGames._game, index)
}

const currentTime = ref("")
const updateCurrentTime = () => {
  currentTime.value = storeGames.getCurrentTime();
};

const updateTimeInterval = setInterval(updateCurrentTime, 100);

onBeforeUnmount(() => clearInterval(updateTimeInterval));


</script>

<template>
  <div>
    <div v-if="storeGames.myPlayerNumber === storeGames._game.currentPlayer" class="text-2xl font-bold mt-6 mb-4">
      Your turn
    </div>
    <div v-else class="text-2xl font-bold mt-6 mb-4">
      {{ storeGames._game['player' + storeGames.opponentPlayerNumber + 'Nickname'] }}'s turn
    </div>

    <Card>
      <CardHeader>
        <CardTitle>Game Status: {{ storeGames.gameStatus }}</CardTitle>
      </CardHeader>
      <CardContent>
        <div :class="{ 'text-green-500': storeGames.myPlayerNumber === 1 }">
          {{ storeGames._game.player1Nickname }} {{ storeGames._game.player1Score }}/{{ storeGames._game.cols *
            storeGames._game.rows / 2 }} pairs found 🃏
        </div>
        <div :class="{ 'text-green-500': storeGames.myPlayerNumber === 2 }">
          {{ storeGames._game.player2Nickname }} {{ storeGames._game.player2Score }}/{{ storeGames._game.cols *
            storeGames._game.rows / 2 }} pairs found 🃏
        </div>
        <div>
          Time: {{ currentTime }}s
        </div>
      </CardContent>
    </Card>

    <div>
      {{ storeGames.myPlayerNumber }}
    </div>
  </div>
  <div class="h-full grid gap-4" :style="{ gridTemplateColumns: `repeat(${storeGames._game.cols}, 1fr)` }">
    <div v-for="(card, index) in storeGames._game.cards" :key="index"
      class="bg-gray-200 rounded-lg flex justify-center items-center cursor-pointer shadow-lg transition-transform transform hover:scale-105"
      :class="{
        'w-16 h-24': storeGames.board.board_cols === 6,
        'w-20 h-28': storeGames.board.board_cols !== 6,
        'bg-white': card.isFlipped || card.isMatched,
        'pointer-events-none': card.isMatched,
      }" @click="flipCardWrapper(index)">
      <span v-if="card.isFlipped || card.isMatched" class="text-xl font-semibold text-gray-900">
        {{ card.value }}
      </span>
    </div>
  </div>
</template>