<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const games = ref([]);

// Handle game rows and track which rows are expanded
const expandedGameIds = ref(new Set());

const fetchGameHistory = async () => {
    try {
        const response = await axios.get('/users/me/history');
        games.value = response.data.data;
    } catch (error) {
        console.error('Error fetching game history:', error);
    }
};

const toggleGameRow = (id) => {
    if (expandedGameIds.value.has(id)) {
        expandedGameIds.value.delete(id);
    } else {
        expandedGameIds.value.add(id);
    }
};

const formatDate = (dateString) => {
    const options = {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    };
    return new Date(dateString).toLocaleString(undefined, options);
};

const transformGameType = (type) => {
    const types = {
        M: 'Multiplayer',
        S: 'Singleplayer',
    };
    return types[type];
};

const transformGameStatus = (status) => {
    const statuses = {
        E: 'Ended',
        PE: 'Pending',
        PL: 'In progress',
        I: 'Interrupted',
    };
    return statuses[status];
};

onMounted(() => {
    fetchGameHistory();
});
</script>

<template>
    <div class="game-history-page">
      <h1 class="text-3xl font-bold mb-4">Game History</h1>
  
      <div v-if="games.length > 0">
        <table class="min-w-full table-auto border-collapse">
          <thead>
            <tr class="bg-gray-100">
              <th class="border-b px-4 py-2"></th>
              <th class="border-b px-4 py-2">Game ID</th>
              <th class="border-b px-4 py-2">Creator</th>
              <th class="border-b px-4 py-2">Game Type</th>
              <th class="border-b px-4 py-2">Board Size</th>
              <th class="border-b px-4 py-2">Game Status</th>
              <th class="border-b px-4 py-2">Start Time</th>
              <th class="border-b px-4 py-2">End Time</th>
              <th class="border-b px-4 py-2">Total Time</th>
            </tr>
          </thead>
          <tbody>
            <template v-for="game in games" :key="game.id">
              <!-- Multiplayer Row with Dropdown Arrow -->
              <tr
                v-if="game.type === 'M'"
                class="cursor-pointer hover:bg-gray-100"
                @click="toggleGameRow(game.id)"
              >
                <td class="border-b px-4 py-2 text-center">
                  <button class="toggle-button">
                    <svg
                      class="arrow-icon"
                      xmlns="http://www.w3.org/2000/svg"
                      :class="{ rotated: expandedGameIds.has(game.id) }"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    >
                      <path d="M9 6l6 6-6 6" />
                    </svg>
                  </button>
                </td>
                <td class="border-b px-4 py-2">{{ game.id }}</td>
                <td class="border-b px-4 py-2">{{ game.creator.nickname }}</td>
                <td class="border-b px-4 py-2">{{ transformGameType(game.type) }}</td>
                <td class="border-b px-4 py-2">{{ game.board_size }}</td>
                <td class="border-b px-4 py-2">{{ transformGameStatus(game.status) }}</td>
                <td class="border-b px-4 py-2">{{ formatDate(game.began_at) }}</td>
                <td class="border-b px-4 py-2">{{ formatDate(game.ended_at) }}</td>
                <td class="border-b px-4 py-2">{{ game.total_time ? game.total_time + 's' : '' }}</td>
              </tr>
  
              <!-- Non-clickable Single-player Row -->
              <tr v-else class="cursor-default hover:bg-gray-100">
                <td class="border-b px-4 py-2"></td>
                <td class="border-b px-4 py-2">{{ game.id }}</td>
                <td class="border-b px-4 py-2">{{ game.creator.nickname }}</td>
                <td class="border-b px-4 py-2">{{ transformGameType(game.type) }}</td>
                <td class="border-b px-4 py-2">{{ game.board_size }}</td>
                <td class="border-b px-4 py-2">{{ transformGameStatus(game.status) }}</td>
                <td class="border-b px-4 py-2">{{ formatDate(game.began_at) }}</td>
                <td class="border-b px-4 py-2">{{ formatDate(game.ended_at) }}</td>
                <td class="border-b px-4 py-2">{{ game.total_time ? game.total_time + 's' : '' }}</td>
              </tr>
  
              <!-- Multiplayer Details Row -->
              <tr v-if="expandedGameIds.has(game.id)">
                <td colspan="9" class="bg-gray-50 px-4 py-2">
                  <div>
                    <p><strong>Players:</strong></p>
                    <div v-for="player in game.players" :key="player.id" class="player-name">
                      <span v-if="player.nickname === game.winner?.nickname">
                        👑 {{ player.nickname }}
                      </span>
                      <span v-else>
                        {{ player.nickname }}
                      </span>
                    </div>
                  </div>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>
  </template>
  

<style scoped>
.toggle-button {
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.arrow-icon {
  transition: transform 0.3s ease;
  width: 24px;
  height: 24px;
}

.arrow-icon.rotated {
  transform: rotate(90deg);
}
</style>
