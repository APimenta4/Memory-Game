<script setup>
import { onMounted } from 'vue';
import { useMemoryGame } from '../singlePlayer/memoryGame.js';
import { useGameStore } from '@/stores/game';
import { useAuthStore } from '@/stores/auth.js';

const storeGame = useGameStore();
const storeAuth = useAuthStore();

onMounted(() => {
    storeGame.board.rows = 4;
    storeGame.board.cols = 3;
});

const { cards, flipCard, resetGame } = useMemoryGame(storeGame.board);
</script>
<template>
    <div>
        <div class="grid gap-4" :style="{ gridTemplateColumns: `repeat(${4}, 1fr)` }">
            <div v-for="(card, index) in cards" :key="index"
                class="w-20 h-28 bg-gray-200 rounded-lg flex justify-center items-center cursor-pointer shadow-lg transition-transform transform hover:scale-105"
                :class="{
                    'bg-white': card.isFlipped || card.isMatched,
                    'pointer-events-none': card.isMatched,
                }" @click="flipCard(index)">
                <span v-if="card.isFlipped || card.isMatched" class="text-xl font-semibold text-gray-900">
                    {{ card.value }}
                </span>
            </div>
        </div>
    </div>
</template>