<template>
    <div class="game-history-page">
      <h1 class="text-3xl font-bold mb-4">Your Game History</h1>
  
      <!-- Game history table -->
      <div v-if="games.length > 0">
        <table class="min-w-full table-auto border-collapse">
          <thead>
            <tr class="bg-gray-100">
              <th class="border-b px-4 py-2">Game Id</th>
              <th class="border-b px-4 py-2">Creator Id</th>
              <th class="border-b px-4 py-2">Winner Id</th>
              <th class="border-b px-4 py-2">Game Type</th>
              <th class="border-b px-4 py-2">Game Status</th>
              <th class="border-b px-4 py-2">Start Time</th>
              <th class="border-b px-4 py-2">End Time</th>
              <th class="border-b px-4 py-2">Total Time</th>
              <th class="border-b px-4 py-2">Board Size</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="game in games" :key="game.id">
                <td class="border-b px-4 py-2">{{ game.id }}</td>
                <td class="border-b px-4 py-2">{{ game.creator_id }}</td>
                <td class="border-b px-4 py-2">{{ game.winner_id }}</td>
                <td class="border-b px-4 py-2">{{ game.type }}</td>
                <td class="border-b px-4 py-2">{{ game.status }}</td> 
                <td class="border-b px-4 py-2">{{ game.began_at }}</td>
                <td class="border-b px-4 py-2">{{ game.ended_at }}</td>
                <td class="border-b px-4 py-2">{{ game.total_time }}</td>
                <td class="border-b px-4 py-2">{{ game.board_cols }}x{{ game.board_rows }}</td>               

            </tr>
            </tbody>
        </table>
      </div>
  
      <!-- If no games are found -->
      <div v-else>
        <p>You have not played any games yet.</p>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, onMounted } from 'vue';
  import { useRouter } from 'vue-router';
  import axios from 'axios';

  const games = ref([]);
  
  const fetchGameHistory = async () => {
    try {
      const response = await axios.get('/users/me/history');
      games.value = response.data.data;
      console.log("Games:" . games)
    } catch (error) {
      console.error('Error fetching game history:', error);
    }
  };

  const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleString(); 
  };
  
  // Call fetchGameHistory when the component is mounted
  onMounted(() => {
    fetchGameHistory();
  });
  </script>
  
  <style scoped>
  .game-history-page {
    padding: 20px;
  }
  
  table {
    width: 100%;
    border-spacing: 0;
    border: 1px solid #ddd;
  }
  
  th, td {
    text-align: left;
    padding: 12px;
  }
  
  th {
    background-color: #f7fafc;
  }
  </style>
  