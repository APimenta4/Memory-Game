<script setup lang="ts">
import { Pie } from "vue-chartjs";
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  ArcElement,
  ChartData,
  ChartOptions,
} from "chart.js";
import { ref, watch } from "vue";


ChartJS.register(Title, Tooltip, Legend, ArcElement);

interface Props {
  labels: string[];
  data: number[];
}


const props = defineProps<Props>();


const pieChartLabels = ref(["Board 3x4", "Board 4x4", "Board 6x6"]);


const chartData = ref<ChartData<"pie", number[], unknown>>({
  labels: pieChartLabels.value, 
  datasets: [
    {
      label: "Percentagem de Jogos por Board",
      backgroundColor: ["#FF6384", "#36A2EB", "#FFCE56"],
      data: props.data,
    },
  ],
});


const options: ChartOptions<"pie"> = {
  responsive: true,
  maintainAspectRatio: false, 
  plugins: {
    legend: {
      position: "top",
    },
    tooltip: {
      enabled: true,
      callbacks: {
        label: function (context) {
          const value = context.raw;
          return `${value}%`; 
        },
      },
    },
  },
};


watch(
  () => props.data,
  (newData) => {
   
    console.log('Atualizando dados do gráfico:', newData);

    chartData.value = {
      labels: pieChartLabels.value, 
      datasets: [
        {
          label: "Percentagem de Jogos por Board",
          backgroundColor: ["#FF6384", "#36A2EB", "#FFCE56"],
          data: newData,
        },
      ],
    };
  },
  { immediate: true }
);
</script>

<template>
  
  <div class="chart-container">
    <Pie :data="chartData" :options="options" />
  </div>
</template>

<style scoped>
.chart-container {
  width: 300px;  
  height: 300px; 
}
</style>
