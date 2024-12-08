<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';

// Data to be fetched
const games = ref([]);
const boards = ref([]);

// Expanded game ID
const expandedGameId = ref(null);

// Pagination
const page = ref(1);
const perPage = 10;

// Filters
const gameType = ref('all');
const status = ref('');
const startDate = ref('');
const endDate = ref('');
const boardId = ref('');
const won = ref(false);

// Sorting
const sortBy = ref('began_at');
const sortOrder = ref('desc');

// Check if given dates are invalid
const isDateInvalid = computed(() => {
  return startDate.value && endDate.value && startDate.value > endDate.value;
});

// Previous/Next for pagination
const handlePageChange = (newPage) => {
  if (newPage >= 1) {
    page.value = newPage;
    fetchGames();
  }
};

// Handle sorting
const handleSort = (sortByField) => {
  if (sortBy.value === sortByField) {
    // If the same column is clicked, inverse the sort order
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortBy.value = sortByField;
    sortOrder.value = 'asc';
  }
  fetchGames();
};

// Fetch games
const fetchGames = async () => {
  if (isDateInvalid.value) return;
  try {
    const params = {
      page: page.value,
      per_page: perPage,
      status: status.value,
      start_date: startDate.value,
      end_date: endDate.value,
      board_id: boardId.value,
      sort_by: sortBy.value,
      sort_order: sortOrder.value,
    };

    if (gameType.value !== 'all') {
      params.type = gameType.value;
    }
    if (gameType.value === 'multiplayer') {
      params.won = won.value ? 1 : 0;
    }

    const response = await axios.get(`/users/me/history`, { params });
    games.value = response.data.data;
  } catch (error) {
    console.error('Error fetching game history:', error);
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

// Toggle game expansion
const toggleGameExpansion = (gameId) => {
  if (expandedGameId.value === gameId) {
    expandedGameId.value = null;
  } else {
    expandedGameId.value = gameId;
  }
};

onMounted(() => {
  fetchGames();
  fetchBoards();
});

// Watch for changes in filters to refetch games and reset pagination
watch([gameType, status, startDate, endDate, boardId, won], () => {
  page.value = 1;
  fetchGames();
});

</script>

<template>
  <div class="game-history-page">
    <h1 class="text-3xl font-bold mb-4">Game History</h1>

    <!-- Filters Section -->
    <div>
      <label for="game-type">Filter by Game Type:</label>
      <select id="game-type" v-model="gameType">
        <option value="all">All</option>
        <option value="multiplayer">Multiplayer</option>
        <option value="singleplayer">Singleplayer</option>
      </select>
    </div>
    <div>
      <label for="status">Filter by Status:</label>
      <select id="status" v-model="status">
        <option value="">All</option>
        <option value="E">Ended</option>
        <option value="PE">Pending</option>
        <option value="PL">In progress</option>
        <option value="I">Interrupted</option>
      </select>
    </div>
    <div>
      <label for="board-size">Filter by Board Size:</label>
      <select id="board-size" v-model="boardId">
        <option value="">All</option>
        <option v-for="board in boards" :key="board.id" :value="board.id">{{ board.board_size }}</option>
      </select>
    </div>
    <div>
      <label for="won">Won:</label>
      <input type="checkbox" id="won" v-model="won" :disabled="gameType !== 'multiplayer'">
    </div>
    <div>
      <label for="start-date">Start Date:</label>
      <input type="datetime-local" id="start-date" v-model="startDate">
      <label for="end-date">End Date:</label>
      <input type="datetime-local" id="end-date" v-model="endDate">
      <p v-if="isDateInvalid" class="text-red-500">Start Date cannot be greater than End Date.</p>
    </div>

    <!-- Games Table Section -->
    <div v-if="games.length > 0">
      <table class="min-w-full table-auto border-collapse">
        <thead>
          <tr class="bg-gray-100">
            <th class="border-b px-4 py-2"></th>
            <th class="border-b px-4 py-2 cursor-pointer" @click="handleSort('id')">
              Game ID
              <span v-if="sortBy === 'id'">
                <span v-if="sortOrder === 'asc'">▲</span>
                <span v-else>▼</span>
              </span>
            </th>
            <th class="border-b px-4 py-2">Creator</th>
            <th class="border-b px-4 py-2">Board Size</th>
            <th class="border-b px-4 py-2">Game Status</th>
            <th class="border-b px-4 py-2 cursor-pointer" @click="handleSort('began_at')">
              Start Time
              <span v-if="sortBy === 'began_at'">
                <span v-if="sortOrder === 'asc'">▲</span>
                <span v-else>▼</span>
              </span>
            </th>
            <th class="border-b px-4 py-2 cursor-pointer" @click="handleSort('total_time')">
              Total Time
              <span v-if="sortBy === 'total_time'">
                <span v-if="sortOrder === 'asc'">▲</span>
                <span v-else>▼</span>
              </span>
            </th>
          </tr>
        </thead>
        <tbody>
          <template v-for="game in games" :key="game.id">
            <tr class="cursor-pointer hover:bg-gray-100" @click="game.type === 'M' && toggleGameExpansion(game.id)">
              <td class="border-b px-4 py-2 text-center">
                <button v-if="game.type === 'M'" class="toggle-button"
                  style="background: none; border: none; cursor: pointer; padding: 4px; display: flex; align-items: center; justify-content: center;">
                  <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg"
                    :class="{ rotated: expandedGameId === game.id }" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    style="transition: transform 0.3s ease; width: 24px; height: 24px;"
                    :style="{ transform: expandedGameId === game.id ? 'rotate(90deg)' : 'rotate(0deg)' }">
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
            <tr v-if="expandedGameId === game.id">
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
        <button @click="handlePageChange(page - 1)" :disabled="page <= 1">Previous</button>
        <span>Page {{ page }}</span>
        <button @click="handlePageChange(page + 1)" :disIabled="games.length < perPage">Next</button>
      </div>
    </div>
  </div>
</template>