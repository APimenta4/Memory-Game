<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const games = ref([]);
const expandedRows = ref(new Set());

const fetchGameHistory = async () => {
    try {
        const response = await axios.get('/users/me/history');
        games.value = response.data.data;
    } catch (error) {
        console.error('Error fetching game history:', error);
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
    if (!dateString) return '';
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

const toggleRow = (id) => {
    if (expandedRows.value.has(id)) {
        expandedRows.value.delete(id);
    } else {
        expandedRows.value.add(id);
    }
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
                        <th class="border-b px-4 py-2"></th> <!-- New dropdown arrow column -->
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
                        <tr v-if="game.type === 'M'" @click="toggleRow(game.id)"
                            class="cursor-pointer hover:bg-gray-100" style="transition: background-color 0.2s ease;">
                            <td class="border-b px-4 py-2 text-center" style="transition: transform 0.2s ease;">
                                <!-- Dropdown arrow with rotation effect -->
                                <span :style="{
                                    transform: expandedRows.has(game.id) ? 'rotate(90deg)' : 'rotate(0deg)',
                                    transition: 'transform 0.2s ease',
                                }">
                                    ▶
                                </span>
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
                        <tr v-else class="cursor-default hover:bg-gray-100"
                            style="transition: background-color 0.2s ease;">
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
                        <tr v-if="expandedRows.has(game.id)">
                            <td colspan="9" class="bg-gray-50 px-4 py-2"
                                style="transition: background-color 0.2s ease;">
                                <div>
                                    <p><strong>Players:</strong> {{ game.players?.map(p => p.nickname).join(', ') || 'No players available' }}</p>
                                    <p><strong>Winner:</strong> {{ game.winner?.nickname || 'No winner' }}</p>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div v-else>
            <p>You have not played any games yet.</p>
        </div>
    </div>
</template>