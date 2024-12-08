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
import { Button } from '@/components/ui/button'
import { Pagination, PaginationEllipsis, PaginationFirst, PaginationLast, PaginationList, PaginationListItem, PaginationNext, PaginationPrev } from '@/components/ui/pagination';

// Data to be fetched
const games = ref([]);
const boards = ref([]);
const totalGames = ref(0);
const totalPages = ref(0);

// Expanded game showing details
const expandedGameId = ref(null);

// Pagination
const page = ref(1);
const perPage = 6;

// Filters
const gameType = ref('');
const status = ref('');
const startDate = ref('');
const endDate = ref('');
const boardId = ref('');
const won = ref(false);

// Default sorting
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
    <div class="flex gap-6">
      <!-- Filters Section -->
      <div class="bg-white shadow rounded-lg p-6 w-1/4 mt-5">
        <h2 class="text-xl font-semibold mb-4">Filters</h2>
        <div class="grid grid-cols-1 gap-4">
          <div>
            <label for="game-type" class="block text-sm font-medium text-gray-700">Filter by Game Type:</label>
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
          <div class="flex items-center space-x-2">
            <input type="checkbox" id="won" v-model="won" :disabled="gameType !== 'multiplayer'"
              class="peer h-4 w-4 shrink-0 rounded-sm border border-primary ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 checked:bg-primary checked:text-primary-foreground" />
            <label for="won"
              class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
              (Multiplayer) Show only games you've won
            </label>
          </div>
          <div>
            <label for="status" class="block text-sm font-medium text-gray-700">Filter by Status:</label>
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
            <label for="board-size" class="block text-sm font-medium text-gray-700">Filter by Board Size:</label>
            <Select v-model="boardId">
              <SelectTrigger>
                <SelectValue :placeholder="'Select a board size'" />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectLabel>Select Board Size</SelectLabel>
                  <SelectItem value="all">All</SelectItem>
                  <SelectItem v-for="board in boards" :key="board.id" :value="String(board.id)">
                    {{ board.board_size }}
                  </SelectItem>
                </SelectGroup>
              </SelectContent>
            </Select>
          </div>
          <div>
            <label for="start-date" class="block text-sm font-medium text-gray-700">Start Date:</label>
            <input type="datetime-local" id="start-date" v-model="startDate"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
          </div>
          <div>
            <label for="end-date" class="block text-sm font-medium text-gray-700">End Date:</label>
            <input type="datetime-local" id="end-date" v-model="endDate"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            <p v-if="isDateInvalid" class="text-red-500 mt-2">Start Date cannot be greater than End Date.</p>
          </div>
        </div>
      </div>

      <!-- Games Table Section -->
      <div class="flex-1">
        <div class="pagination flex justify-center mb-3 mt-4">
          <Pagination :total="totalPages * perPage" :sibling-count="1" show-edges :default-page="page"
            @update:page="handlePageChange">
            <PaginationList v-slot="{ items }" class="flex items-center gap-1">
              <PaginationFirst />
              <PaginationPrev />
              <template v-for="(item, index) in items">
                <PaginationListItem v-if="item.type === 'page'" :key="index" :value="item.value" as-child>
                  <Button class="w-9 h-9 p-0" :variant="item.value === page ? 'default' : 'outline'">
                    {{ item.value }}
                  </Button>
                </PaginationListItem>
                <PaginationEllipsis v-else :key="item.type" :index="index" />
              </template>

              <PaginationNext />
              <PaginationLast />
            </PaginationList>
          </Pagination>
        </div>

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
                <TableRow :class="{ 'cursor-pointer': game.type === 'M', 'hover:bg-gray-100': game.type === 'M' }"
                  @click="game.type === 'M' && toggleGameExpansion(game.id)">
                  <TableCell class="text-center">
                    <button :style="{
                      background: 'none',
                      border: 'none',
                      cursor: game.type === 'M' ? 'pointer' : 'default',
                      padding: '4px',
                      display: 'flex',
                      alignItems: 'center',
                      justifyContent: 'center',
                      visibility: game.type === 'M' ? 'visible' : 'hidden',
                      pointerEvents: game.type === 'M' ? 'auto' : 'none'
                    }">
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
        </div>
        <div v-else class="mt-6 ml-5">
          <p>No games matching the chosen criteria were found :(</p>
        </div>
      </div>
    </div>
  </div>
</template>