<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectLabel,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';

// Data to be fetched
const games = ref([]);
const boards = ref([]);
const totalGames = ref(0);
const totalPages = ref(0); // Store total number of pages

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
  if (newPage >= 1 && newPage <= totalPages.value) {
    page.value = newPage;
    fetchGames();
  }
};

// Handle sorting
const handleSort = (sortByField) => {
  if (sortBy.value === sortByField) {
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
      sort_by: sortBy.value,
      sort_order: sortOrder.value,
    };

    if (startDate.value) {
      params.start_date = startDate.value;
    }
    if (endDate.value) {
      params.end_date = endDate.value;
    }
    if (gameType.value && gameType.value !== 'all') {
      params.type = gameType.value;
    }
    if (boardId.value && boardId.value !== 'all') {
      params.board_id = boardId.value;
    }
    if (status.value && status.value !== 'all') {
      params.status = status.value;
    }
    if (gameType.value === 'multiplayer') {
      params.won = won.value ? 1 : 0;
    }

    const response = await axios.get(`/users/me/history`, { params });
    games.value = response.data.data;
    totalGames.value = response.data.meta.total;
    totalPages.value = totalGames.value ? Math.ceil(totalGames.value / perPage) : 0;
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

// Change data format
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
  expandedGameId.value = expandedGameId.value === gameId ? null : gameId;
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
      <Select v-model="gameType">
        <SelectTrigger>
          <SelectValue :placeholder="'Select a game type'" />
        </SelectTrigger>
        <SelectContent>
          <SelectGroup>
            <SelectLabel>Select Game Type</SelectLabel>
            <SelectItem value="all">All</SelectItem>
            <SelectItem value="multiplayer">Multiplayer</SelectItem>
            <SelectItem value="singleplayer">Singleplayer</SelectItem>
          </SelectGroup>
        </SelectContent>
      </Select>
    </div>
    <div>
      <label for="status">Filter by Status:</label>
      <Select v-model="status">
        <SelectTrigger>
          <SelectValue :placeholder="'Select a status'" />
        </SelectTrigger>
        <SelectContent>
          <SelectGroup>
            <SelectLabel>Select Status</SelectLabel>
            <SelectItem value="all">All</SelectItem>
            <SelectItem value="E">Ended</SelectItem>
            <SelectItem value="PE">Pending</SelectItem>
            <SelectItem value="PL">In progress</SelectItem>
            <SelectItem value="I">Interrupted</SelectItem>
          </SelectGroup>
        </SelectContent>
      </Select>
    </div>
    <div>
      <label for="board-size">Filter by Board Size:</label>
      <Select v-model="boardId">
        <SelectTrigger>
          <SelectValue :placeholder="'Select a board size'" />
        </SelectTrigger>
        <SelectContent>
          <SelectGroup>
            <SelectLabel>Select Board Size</SelectLabel>
            <SelectItem value="all">All</SelectItem>
            <SelectItem v-for="board in boards" :key="board.id" :value="board.id">
              {{ board.board_size }}
            </SelectItem>
          </SelectGroup>
        </SelectContent>
      </Select>
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
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead></TableHead>
            <TableHead class="cursor-pointer" @click="handleSort('id')">
              Game ID
              <span v-if="sortBy === 'id'">
                <span v-if="sortOrder === 'asc'">▲</span>
                <span v-else>▼</span>
              </span>
            </TableHead>
            <TableHead>Creator</TableHead>
            <TableHead>Board Size</TableHead>
            <TableHead>Game Status</TableHead>
            <TableHead class="cursor-pointer" @click="handleSort('began_at')">
              Start Time
              <span v-if="sortBy === 'began_at'">
                <span v-if="sortOrder === 'asc'">▲</span>
                <span v-else>▼</span>
              </span>
            </TableHead>
            <TableHead class="cursor-pointer" @click="handleSort('total_time')">
              Total Time
              <span v-if="sortBy === 'total_time'">
                <span v-if="sortOrder === 'asc'">▲</span>
                <span v-else>▼</span>
              </span>
            </TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <template v-for="game in games" :key="game.id">
            <TableRow class="cursor-pointer hover:bg-gray-100" @click="game.type === 'M' && toggleGameExpansion(game.id)">
              <TableCell class="text-center">
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
              </TableCell>
              <TableCell>{{ game.id }}</TableCell>
              <TableCell>{{ game.creator.nickname }}</TableCell>
              <TableCell>{{ game.board_size }}</TableCell>
              <TableCell>{{ transformGameStatus(game.status) }}</TableCell>
              <TableCell>{{ formatDate(game.began_at) }}</TableCell>
              <TableCell>{{ game.total_time ? game.total_time + 's' : '' }}</TableCell>
            </TableRow>
            <TableRow v-if="expandedGameId === game.id">
              <TableCell colspan="9" class="bg-gray-50">
                <div>
                  <p><strong>Players:</strong></p>
                  <div v-for="player in game.players" :key="player.id" class="player-name">
                    <span>
                      <span v-if="player.nickname === game.winner?.nickname">👑</span>
                      {{ player.nickname }} - {{ player.pivot?.pairs_discovered || 0 }} Pairs Discovered
                    </span>
                  </div>
                </div>
              </TableCell>
            </TableRow>
          </template>
        </TableBody>
      </Table>
      <div class="pagination">
        <button @click="handlePageChange(page - 1)" :disabled="page <= 1">Previous</button>
        <span>Page {{ page }} of {{ totalPages }}</span>
        <button @click="handlePageChange(page + 1)" :disabled="page >= totalPages">Next</button>
      </div>
    </div>
    <div v-else>
      <p>No games matching the chosen criteria were found :(</p>
    </div>
  </div>
</template>