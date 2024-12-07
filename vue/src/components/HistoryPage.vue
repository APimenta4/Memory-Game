<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const multiplayerGames = ref([]);
const singleplayerGames = ref([]);

const expandedMultiplayerGameIds = ref(new Set());

// State for pagination
const multiplayerPage = ref(1);
const singleplayerPage = ref(1);

const multiplayerPerPage = 10;
const singleplayerPerPage = 10;

// Handle Multiplayer Pagination
const handleMultiplayerPageChange = (newPage) => {
  if (newPage >= 1) {
    multiplayerPage.value = newPage;
    fetchMultiplayerGames();
  }
};

// Handle Singleplayer Pagination
const handleSingleplayerPageChange = (newPage) => {
  if (newPage >= 1) {
    singleplayerPage.value = newPage;
    fetchSingleplayerGames();
  }
};



  // Fetch Multiplayer games with pagination
  const fetchMultiplayerGames = async () => {
    try {
      const response = await axios.get(`/users/me/history/multiplayer?page=${multiplayerPage.value}&per_page=${multiplayerPerPage}`);
      multiplayerGames.value = response.data.data;
    } catch (error) {
      console.error('Error fetching multiplayer game history:', error);
    }
  };
  
  // Fetch Singleplayer games with pagination
  const fetchSingleplayerGames = async () => {
    try {
      const response = await axios.get(`/users/me/history/singleplayer?page=${singleplayerPage.value}&per_page=${singleplayerPerPage}`);
      singleplayerGames.value = response.data.data;
    } catch (error) {
      console.error('Error fetching single-player game history:', error);
    }
  };

const toggleGameRow = (id, expandedSet) => {
  if (expandedSet.has(id)) {
    expandedSet.delete(id);
  } else {
    expandedSet.add(id);
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
  fetchMultiplayerGames();
  fetchSingleplayerGames();
});
</script>


<template>
  <div class="game-history-page">
    <h1 class="text-3xl font-bold mb-4">Game History</h1>

    <!-- Multiplayer Games Section -->
    <h2 class="text-2xl font-bold mt-8 mb-4">Multiplayer Games</h2>
    <div v-if="multiplayerGames.length > 0">
      <table class="min-w-full table-auto border-collapse">
        <thead>
          <tr class="bg-gray-100">
            <th class="border-b px-4 py-2"></th>
            <th class="border-b px-4 py-2">Game ID</th>
            <th class="border-b px-4 py-2">Creator</th>
            <th class="border-b px-4 py-2">Board Size</th>
            <th class="border-b px-4 py-2">Game Status</th>
            <th class="border-b px-4 py-2">Start Time</th>
            <th class="border-b px-4 py-2">Total Time</th>
          </tr>
        </thead>
        <tbody>
          <template v-for="game in multiplayerGames" :key="game.id">
            <tr class="cursor-pointer hover:bg-gray-100" @click="toggleGameRow(game.id, expandedMultiplayerGameIds)">
              <td class="border-b px-4 py-2 text-center">
                <button class="toggle-button">
                  <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg"
                    :class="{ rotated: expandedMultiplayerGameIds.has(game.id) }" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 6l6 6-6 6" />
                  </svg>
                </button>
              </td>
              <td class="border-b px-4 py-2">{{ game.id }}</td>
              <td class="border-b px-4 py-2">{{ game.creator.nickname }}</td>
              <td class="border-b px-4 py-2">{{ game.board_size }}</td>
              <td class="border-b px-4 py-2">{{ transformGameStatus(game.status) }}</td>
              <td class="border-b px-4 py-2">{{ formatDate(game.began_at) }}</td>
              <td class="border-b px-4 py-2">{{ game.total_time ? game.total_time + 's' : '' }}</td>
            </tr>
            <tr v-if="expandedMultiplayerGameIds.has(game.id)">
              <td colspan="9" class="bg-gray-50 px-4 py-2">
                <div>
                  <p><strong>Players:</strong></p>
                  <div v-for="player in game.players" :key="player.id" class="player-name">
                    <span>
                      <span v-if="player.nickname === game.winner?.nickname">👑</span>
                      {{ player.nickname }} - {{ player.pivot?.pairs_discovered || 0 }} Pairs Discovered
                    </span>
                  </div>
                </div>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
      <div class="pagination">
        <button @click="handleMultiplayerPageChange(multiplayerPage - 1)"
          :disabled="multiplayerPage <= 1">Previous</button>
        <span>Page {{ multiplayerPage }}</span>
        <button @click="handleMultiplayerPageChange(multiplayerPage + 1)"
          :disabled="multiplayerGames.length < multiplayerPerPage">Next</button>
      </div>
    </div>
  </div>

  <!-- Single-player Games Section -->
  <h2 class="text-2xl font-bold mt-8 mb-4">Singleplayer Games</h2>
  <div v-if="singleplayerGames.length > 0">
    <table class="min-w-full table-auto border-collapse">
      <thead>
        <tr class="bg-gray-100">
          <th class="border-b px-4 py-2">Game ID</th>
          <th class="border-b px-4 py-2">Board Size</th>
          <th class="border-b px-4 py-2">Game Status</th>
          <th class="border-b px-4 py-2">Start Time</th>
          <th class="border-b px-4 py-2">Total Time</th>
        </tr>
      </thead>
      <tbody>
        <template v-for="game in singleplayerGames" :key="game.id">
          <tr class="cursor-default hover:bg-gray-100">
            <td class="border-b px-4 py-2">{{ game.id }}</td>
            <td class="border-b px-4 py-2">{{ game.board_size }}</td>
            <td class="border-b px-4 py-2">{{ transformGameStatus(game.status) }}</td>
            <td class="border-b px-4 py-2">{{ formatDate(game.began_at) }}</td>
            <td class="border-b px-4 py-2">{{ game.total_time ? game.total_time + 's' : '' }}</td>
          </tr>
        </template>
      </tbody>
    </table>
    <div class="pagination">
      <button @click="handleSingleplayerPageChange(singleplayerPage - 1)"
        :disabled="singleplayerPage <= 1">Previous</button>
      <span>Page {{ singleplayerPage }}</span>
      <button @click="handleSingleplayerPageChange(singleplayerPage + 1)"
        :disabled="singleplayerGames.length < singleplayerPerPage">Next</button>
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
