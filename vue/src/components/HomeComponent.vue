<script setup>
import router from '@/router';
import BuyCoins from '@/components/PurchaseCoins.vue'
import { useAuthStore } from '@/stores/auth';

const authstore = useAuthStore();

const navigateTo = (route) => {
  router.push(route);
};
</script>

<template>
  <div class="flex flex-col items-center text-center space-y-8 py-12">
    <!-- Logo and Header -->
    <div class="w-full px-4">
      <img src="/estg_h-01.png" alt="ESTG Logo" class="w-48 h-auto object-contain mx-auto" />
      <h1 class="text-3xl font-bold text-gray-900 sm:text-4xl">Memory Game</h1>
    </div>

    <!-- Login Section -->
    <div 
      class="max-w-2xl mx-auto px-4 space-y-4"
      v-if="!authstore.user"
    >
      <h2 class="text-xl font-semibold text-gray-800">Login</h2>
      <button 
        class="w-full py-3 px-6 bg-gray-600 text-white rounded-lg shadow hover:bg-gray-700 transition"
        @click="navigateTo('/login')">
        Login
      </button>
    </div>
    <!-- Horizontal Sections for Categories, Games, and Scoreboard -->
    <div class="max-w-full lg:max-w-2xl mx-auto px-4 space-y-8">
  <div class="flex flex-col lg:flex-row justify-center lg:space-x-8 space-y-8 lg:space-y-0">
    <!-- Games Section -->
    <div class="mx-auto px-4 space-y-4">
      <h2 class="text-xl font-semibold text-gray-800">Games</h2>
      <button 
        v-if="!(authstore.user && authstore.user.type === 'A')"
        class="w-full py-3 px-6 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition"
        @click="navigateTo('/singleplayer')">
        Singleplayer
      </button>
      <button 
        class="w-full py-3 px-6 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition"
        @click="navigateTo('multiplayer')">
        Multiplayer
      </button>
    </div>
    
    <!-- Scoreboard Section -->
    <div class="mx-auto px-4 space-y-4">
      <h2 class="text-xl font-semibold text-gray-800">Scoreboard</h2>
      <button 
        v-if="(authstore.user && authstore.user.type != 'A')"
        class="w-full py-3 px-6 bg-purple-600 text-white rounded-lg shadow hover:bg-purple-700 transition"
        @click="navigateTo('/scoreboard/personal')">
        Scoreboard Personal
      </button>
      <button 
        class="w-full py-3 px-6 bg-purple-600 text-white rounded-lg shadow hover:bg-purple-700 transition"
        @click="navigateTo('/scoreboard/global')">
        Scoreboard Global
      </button>
    </div>
    
    <!-- History Section -->
    <div v-show="authstore.user" class="mx-auto px-4 space-y-4">
      <h2 class="text-xl font-semibold text-gray-800">History</h2>
      <button 
        class="w-full py-3 px-6 bg-orange-600 text-white rounded-lg shadow hover:bg-orange-700 transition"
        @click="navigateTo('/history')">
        Games History
      </button>
      <button 
        class="w-full py-3 px-6 bg-orange-600 text-white rounded-lg shadow hover:bg-orange-700 transition"
        @click="navigateTo('/transactions/history')">
        Transactions History
      </button>
    </div>
  </div>
</div>

  </div>
</template>