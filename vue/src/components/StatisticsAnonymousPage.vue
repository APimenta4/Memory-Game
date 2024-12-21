<script setup lang="ts">
import axios from "axios";
import { ref, onMounted, computed } from "vue";

import BarChart from "@/components/ui/BarChart.vue";  


interface Stats {
  games_completed_last_7_days?: number;
  games_completed_last_month?: number;
  games_completed_last_year?: number;
  [key: string]: any;
}



const filteredStats = computed(() => {
  const keysToShow = [
    "All users total games completed",
    "Total users registered",
  ];
  return Object.fromEntries(
    Object.entries(stats.value).filter(([key]) => keysToShow.includes(key))
  );
});



const stats = ref<Stats>({});
const errorMessage = ref("");

const barChartLabels = ref<string[]>(["Last 7 days", "Last month", "Last year"]);
const barChartData = ref<number[]>([]);

async function fetchStatistics() {
  try {
    const response = await axios.get("/statistics");

    stats.value = response.data;

    barChartData.value = [
      stats.value.games_completed_last_7_days || 0,
      stats.value.games_completed_last_month || 0,
      stats.value.games_completed_last_year || 0,
    ];


  } catch (error) {
    errorMessage.value = error.response?.data?.error || "Erro ao carregar estatísticas.";
  }
}

onMounted(fetchStatistics);
</script>

<template>
  <div class="statistics-page">
    <h1 class="text-xl font-bold mb-4">Statistics</h1>
    <div v-if="errorMessage" class="text-red-500">{{ errorMessage }}</div>

    <div v-else>
      <div class="charts-container mb-6">
       
        <div class="chart-item">
          <h2 class="text-lg font-semibold mb-2">Number of games played over time by all users </h2>
          <BarChart :labels="barChartLabels" :data="barChartData" />
        </div>
      </div>

       
      <div class="statistics-list">
        <h2 class="text-lg font-semibold mb-2">Statistics Details</h2>
        <ul>
          <li v-for="(value, key) in filteredStats" :key="key">
            <strong>{{ key }}:</strong> {{ value }}
          </li>
        </ul>
      </div>

    </div>
  </div>
</template>

<style>
.statistics-page {
  max-width: 1050px;
  margin: auto;
  padding: 20px;
}

.charts-container {
  display: flex;
  gap: 20px;
  justify-content: space-between;
}

.chart-item {
  flex: 1;
}

.statistics-list {
  margin-top: 20px;
  text-align: left;
  width: 80%;
  margin: 0 auto;
}
</style>
