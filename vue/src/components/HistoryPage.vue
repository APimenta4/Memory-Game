<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

// Data to be fetched
const multiplayerGames = ref([]);
const singleplayerGames = ref([]);
const boards = ref([]);

// Multiplayer table rows expanded (to show details)
const expandedMultiplayerGameIds = ref(new Set());

// Pagination
const multiplayerPage = ref(1);
const singleplayerPage = ref(1);
const multiplayerPerPage = 10;
const singleplayerPerPage = 10;

// Filters
const multiplayerStatus = ref('');
const singleplayerStatus = ref('');
const multiplayerStartDate = ref('');
const multiplayerEndDate = ref('');
const singleplayerStartDate = ref('');
const singleplayerEndDate = ref('');
const multiplayerBoardId = ref('');
const singleplayerBoardId = ref('');
const multiplayerWon = ref(false);

// Sorting
const multiplayerSortBy = ref('began_at');
const multiplayerSortOrder = ref('desc');
const singleplayerSortBy = ref('began_at');
const singleplayerSortOrder = ref('desc');

// Check if given dates are invalid
const isMultiplayerDateInvalid = computed(() => {
  return multiplayerStartDate.value && multiplayerEndDate.value && multiplayerStartDate.value > multiplayerEndDate.value;
});

const isSingleplayerDateInvalid = computed(() => {
  return singleplayerStartDate.value && singleplayerEndDate.value && singleplayerStartDate.value > singleplayerEndDate.value;
});

// Previous/Next for multiplayer pagination
const handleMultiplayerPageChange = (newPage) => {
  if (newPage >= 1) {
    multiplayerPage.value = newPage;
    fetchMultiplayerGames();
  }
};

// Previous/Next for singleplayer pagination
const handleSingleplayerPageChange = (newPage) => {
  if (newPage >= 1) {
    singleplayerPage.value = newPage;
    fetchSingleplayerGames();
  }
};

// Handle sorting
const handleMultiplayerSort = (sortBy) => {
  if (multiplayerSortBy.value === sortBy) {
    // If the same column is clicked, inverse the sort order
    multiplayerSortOrder.value = multiplayerSortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    multiplayerSortBy.value = sortBy;
    multiplayerSortOrder.value = 'asc';
  }
  fetchMultiplayerGames();
};

const handleSingleplayerSort = (sortBy) => {
  if (singleplayerSortBy.value === sortBy) {
    // If the same column is clicked, inverse the sort order
    singleplayerSortOrder.value = singleplayerSortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    singleplayerSortBy.value = sortBy;
    singleplayerSortOrder.value = 'asc';
  }
  fetchSingleplayerGames();
};

// Fetch Multiplayer games
const fetchMultiplayerGames = async () => {
  if (isMultiplayerDateInvalid.value) return;
  try {
    const response = await axios.get(`/users/me/history/multiplayer`, {
      params: {
        page: multiplayerPage.value,
        per_page: multiplayerPerPage,
        status: multiplayerStatus.value,
        start_date: multiplayerStartDate.value,
        end_date: multiplayerEndDate.value,
        board_id: multiplayerBoardId.value,
        won: multiplayerWon.value ? 1 : 0,
        sort_by: multiplayerSortBy.value,
        sort_order: multiplayerSortOrder.value,
      },
    });
    multiplayerGames.value = response.data.data;
  } catch (error) {
    console.error('Error fetching multiplayer game history:', error);
  }
};

// Fetch Singleplayer games
const fetchSingleplayerGames = async () => {
  if (isSingleplayerDateInvalid.value) return;
  try {
    const response = await axios.get(`/users/me/history/singleplayer`, {
      params: {
        page: singleplayerPage.value,
        per_page: singleplayerPerPage,
        status: singleplayerStatus.value,
        start_date: singleplayerStartDate.value,
        end_date: singleplayerEndDate.value,
        board_id: singleplayerBoardId.value,
        sort_by: singleplayerSortBy.value,
        sort_order: singleplayerSortOrder.value,
      },
    });
    singleplayerGames.value = response.data.data;
  } catch (error) {
    console.error('Error fetching single-player game history:', error);
  }
};

// Fetch available boards
const fetchBoards = async () => {
  try {
    const response = await axios.get('/boards');
    boards.value = response.data.data;
  } catch (error) {
    console.error('Error fetching boards:', error);
  }
};

// Expand row
const toggleGameRow = (id, expandedSet) => {
  if (expandedSet.has(id)) {
    expandedSet.delete(id);
  } else {
    expandedSet.add(id);
  }
};

// Change data format (passing undefined to LocaleString lets javascript decide the format)
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

// Map statuses
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
  fetchBoards();
});
</script>

<template>
  <div class="game-history-page">
    <h1 class="text-3xl font-bold mb-4">Game History</h1>

    <!-- Multiplayer Games Section -->
    <h2 class="text-2xl font-bold mt-8 mb-4">Multiplayer Games</h2>
    <div>
      <label for="multiplayer-status">Filter by Status:</label>
      <select id="multiplayer-status" v-model="multiplayerStatus" @change="fetchMultiplayerGames">
        <option value="">All</option>
        <option value="E">Ended</option>
        <option value="PE">Pending</option>
        <option value="PL">In progress</option>
        <option value="I">Interrupted</option>
      </select>
    </div>
    <div>
      <label for="multiplayer-board-size">Filter by Board Size:</label>
      <select id="multiplayer-board-size" v-model="multiplayerBoardId" @change="fetchMultiplayerGames">
        <option value="">All</option>
        <option v-for="board in boards" :key="board.id" :value="board.id">{{ board.board_size }}</option>
      </select>
    </div>
    <div>
      <label for="multiplayer-won">Won:</label>
      <input type="checkbox" id="multiplayer-won" v-model="multiplayerWon" @change="fetchMultiplayerGames">
    </div>
    <div>
      <label for="multiplayer-start-date">Start Date:</label>
      <input type="datetime-local" id="multiplayer-start-date" v-model="multiplayerStartDate" @change="fetchMultiplayerGames">
      <label for="multiplayer-end-date">End Date:</label>
      <input type="datetime-local" id="multiplayer-end-date" v-model="multiplayerEndDate" @change="fetchMultiplayerGames">
      <p v-if="isMultiplayerDateInvalid" class="text-red-500">Start Date cannot be greater than End Date.</p>
    </div>
    <div v-if="multiplayerGames.length > 0">
      <table class="min-w-full table-auto border-collapse">
        <thead>
          <tr class="bg-gray-100">
            <th class="border-b px-4 py-2"></th>
            <th class="border-b px-4 py-2 cursor-pointer" @click="handleMultiplayerSort('id')">
              Game ID
              <span v-if="multiplayerSortBy === 'id'">
                <span v-if="multiplayerSortOrder === 'asc'">▲</span>
                <span v-else>▼</span>
              </span>
            </th>
            <th class="border-b px-4 py-2">Creator</th>
            <th class="border-b px-4 py-2">Board Size</th>
            <th class="border-b px-4 py-2">Game Status</th>
            <th class="border-b px-4 py-2 cursor-pointer" @click="handleMultiplayerSort('began_at')">
              Start Time
              <span v-if="multiplayerSortBy === 'began_at'">
                <span v-if="multiplayerSortOrder === 'asc'">▲</span>
                <span v-else>▼</span>
              </span>
            </th>
            <th class="border-b px-4 py-2 cursor-pointer" @click="handleMultiplayerSort('total_time')">
              Total Time
              <span v-if="multiplayerSortBy === 'total_time'">
                <span v-if="multiplayerSortOrder === 'asc'">▲</span>
                <span v-else>▼</span>
              </span>
            </th>
          </tr>
        </thead>
        <tbody>
          <template v-for="game in multiplayerGames" :key="game.id">
            <tr class="cursor-pointer hover:bg-gray-100" @click="toggleGameRow(game.id, expandedMultiplayerGameIds)">
              <td class="border-b px-4 py-2 text-center">
                <button class="toggle-button" style="background: none; border: none; cursor: pointer; padding: 4px; display: flex; align-items: center; justify-content: center;">
                  <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg"
                    :class="{ rotated: expandedMultiplayerGameIds.has(game.id) }" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="transition: transform 0.3s ease; width: 24px; height: 24px;"
                    :style="{ transform: expandedMultiplayerGameIds.has(game.id) ? 'rotate(90deg)' : 'rotate(0deg)' }">
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
  <div>
    <label for="singleplayer-status">Filter by Status:</label>
    <select id="singleplayer-status" v-model="singleplayerStatus" @change="fetchSingleplayerGames">
      <option value="">All</option>
      <option value="E">Ended</option>
      <option value="PE">Pending</option>
      <option value="PL">In progress</option>
      <option value="I">Interrupted</option>
    </select>
  </div>
  <div>
    <label for="singleplayer-board-size">Filter by Board Size:</label>
    <select id="singleplayer-board-size" v-model="singleplayerBoardId" @change="fetchSingleplayerGames">
      <option value="">All</option>
      <option v-for="board in boards" :key="board.id" :value="board.id">{{ board.board_size }}</option>
    </select>
  </div>
  <div>
    <label for="singleplayer-start-date">Start Date:</label>
    <input type="datetime-local" id="singleplayer-start-date" v-model="singleplayerStartDate" @change="fetchSingleplayerGames">
    <label for="singleplayer-end-date">End Date:</label>
    <input type="datetime-local" id="singleplayer-end-date" v-model="singleplayerEndDate" @change="fetchSingleplayerGames">
    <p v-if="isSingleplayerDateInvalid" class="text-red-500">Start Date cannot be greater than End Date.</p>
  </div>
  <div v-if="singleplayerGames.length > 0">
    <table class="min-w-full table-auto border-collapse">
      <thead>
        <tr class="bg-gray-100">
          <th class="border-b px-4 py-2 cursor-pointer" @click="handleSingleplayerSort('id')">
            Game ID
            <span v-if="singleplayerSortBy === 'id'">
              <span v-if="singleplayerSortOrder === 'asc'">▲</span>
              <span v-else>▼</span>
            </span>
          </th>
          <th class="border-b px-4 py-2">Board Size</th>
          <th class="border-b px-4 py-2">Game Status</th>
          <th class="border-b px-4 py-2 cursor-pointer" @click="handleSingleplayerSort('began_at')">
            Start Time
            <span v-if="singleplayerSortBy === 'began_at'">
              <span v-if="singleplayerSortOrder === 'asc'">▲</span>
              <span v-else>▼</span>
            </span>
          </th>
          <th class="border-b px-4 py-2 cursor-pointer" @click="handleSingleplayerSort('total_time')">
            Total Time
            <span v-if="singleplayerSortBy === 'total_time'">
              <span v-if="singleplayerSortOrder === 'asc'">▲</span>
              <span v-else>▼</span>
            </span>
          </th>
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