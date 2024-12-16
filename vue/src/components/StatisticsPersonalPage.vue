<script setup lang="ts">
import axios from "axios";
import { ref, onMounted } from "vue";

const stats = ref({});
const errorMessage = ref("");

async function fetchStatistics() {
  try {
    const response = await axios.get("/statistics/personal");
    stats.value = response.data;
  } catch (error) {
    errorMessage.value = error.response?.data?.error || "Erro ao carregar estatísticas.";
  }
}

onMounted(fetchStatistics);
</script>

<template>
  <div class="statistics-page">
    <h1 class="text-xl font-bold mb-4">Estatísticas</h1>
    <div v-if="errorMessage" class="text-red-500">{{ errorMessage }}</div>

    <div v-else>
      <div v-for="(value, key) in stats" :key="key" class="mb-4">
        <h2 class="text-lg font-semibold">{{ key }}</h2>
        <p>{{ value }}</p>
      </div>
    </div>
  </div>
</template>

<style>
.statistics-page {
  max-width: 800px;
  margin: auto;
  padding: 20px;
}
</style>