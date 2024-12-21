<script setup lang="ts">
import axios from "axios";
import { ref, onMounted, computed } from "vue";

import BarChart from "@/components/ui/BarChart.vue";
import PieChart from "@/components/ui/PieChart.vue";  
import LineChart from "@/components/ui/LineChart.vue";  


interface Stats {
  percentage_games_on_board_1?: number;
  percentage_games_on_board_2?: number;
  percentage_games_on_board_3?: number;
  games_completed_last_7_days?: number;
  games_completed_last_month?: number;
  games_completed_last_year?: number;
  [key: string]: any;
}



const filteredStats = computed(() => {
  const keysToShow = [
    "All users total games completed",
    "Total users registered",
    "Total spent by all users (€)",
  ];
  return Object.fromEntries(
    Object.entries(stats.value).filter(([key]) => keysToShow.includes(key))
  );
});



const stats = ref<Stats>({});
const errorMessage = ref("");

const barChartLabels = ref<string[]>(["Last 7 days", "Last month", "Last year"]);
const barChartData = ref<number[]>([]);
const pieChartLabels = ref<string[]>([]);
const pieChartData = ref<number[]>([]);
const lineChartLabels = ref<string[]>([]);
const lineChartData = ref<number[]>([]);
const lineChartDynamicLabels = ref<string[]>([]);
const lineChartDynamicData = ref<number[]>([]);


async function fetchStatistics() {
  try {
    const response = await axios.get("/statistics/admin");

    stats.value = response.data;

    barChartData.value = [
      stats.value.games_completed_last_7_days || 0,
      stats.value.games_completed_last_month || 0,
      stats.value.games_completed_last_year || 0,
    ];

    pieChartLabels.value = ["Board 1", "Board 2", "Board 3"];
    pieChartData.value = [
      stats.value.percentage_games_on_board_1 || 0,
      stats.value.percentage_games_on_board_2 || 0,
      stats.value.percentage_games_on_board_3 || 0,
    ];

    lineChartLabels.value = ["Last week", "Last month", "Last year"];
    lineChartData.value = [
      stats.value.all_purchases_last_week || 0,
      stats.value.all_purchases_last_month || 0,
      stats.value.all_purchases_last_year || 0,
    ];

    lineChartDynamicLabels.value = ["Today","Last 7 days", "This month", "This year"];
    lineChartDynamicData.value = [
      stats.value.all_purchases_today || 0,
      stats.value.all_purchases_last_7_days || 0,
      stats.value.all_purchases_this_month || 0,
      stats.value.all_purchases_this_year || 0,
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
          <h2 class="text-lg font-semibold mb-2">Percentage of games completed by all users each board</h2>
          <PieChart :labels="pieChartLabels" :data="pieChartData" />
        </div>
        <div class="chart-item">
          <h2 class="text-lg font-semibold mb-2">Number of games played over time by all users </h2>
          <BarChart :labels="barChartLabels" :data="barChartData" />
        </div>

        <div class="chart-item">
          <h2 class="text-lg font-semibold mb-2">Purchases by all users - Past Statistics</h2>
          <LineChart :labels="lineChartLabels" :data="lineChartData" />
        </div>

        <div class="chart-item">
          <h2 class="text-lg font-semibold mb-2">Purchases by all users - Dynamic Statistics</h2>
          <LineChart :labels="lineChartDynamicLabels" :data="lineChartDynamicData" />
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
  flex-direction: row; 
  justify-content: center; 
  gap: 20px; 
  flex-wrap: wrap; 
}

.chart-item {
  flex: 1;
  min-width: 400px; 
  max-width: 500px; 
  text-align: center;
}

.statistics-list {
  margin-top: 20px;
  text-align: left;
  width: 80%;
  margin: 0 auto;
}
</style>

