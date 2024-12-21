<script setup lang="ts">
import { Bar } from "vue-chartjs";
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
  ChartData,
  ChartOptions,
} from "chart.js";
import {ref, watch} from "vue";

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

interface Props {
  labels: string[];
  data: number[];
}

const props = defineProps<Props>();


const barChartData = ref<ChartData<"bar", number[], string>>({
  labels: props.labels,
  datasets: [
    {
      label: "Games played",
      backgroundColor: "#36A2EB",
      data: props.data,
    },
  ],
});


const options: ChartOptions<"bar"> = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: "top",
    },
    tooltip: {
      callbacks: {
        label: function (context) {
          return `${context.raw} Games`; 
        },
      },
    },
  },
};

watch(
  () => props.data,
  (newData) => {
   
    barChartData.value = {
      labels: props.labels,
      datasets: [
        {
          label: "Games played",
          backgroundColor: "#36A2EB",
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
    <Bar :data="barChartData" :options="options" />
  </div>
</template>

<style scoped>
.chart-container {
  width: 470px;
  height: 300px;
}
</style>
