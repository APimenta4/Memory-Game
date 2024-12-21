<script setup lang="ts">
import { Line } from "vue-chartjs";
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  LineElement,
  PointElement,
  CategoryScale,
  LinearScale,
  ChartData,
  ChartOptions,
} from "chart.js";
import { ref, watch } from "vue";

ChartJS.register(
  Title,
  Tooltip,
  Legend,
  LineElement,
  PointElement,
  CategoryScale,
  LinearScale
);

interface Props {
  labels: string[];
  data: number[];
}

const props = defineProps<Props>();

const lineChartData = ref<ChartData<"line", number[], string>>({
  labels: props.labels,
  datasets: [
    {
      label: "Purchases over time",
      backgroundColor: "rgba(54, 162, 235, 0.2)",
      borderColor: "#36A2EB",
      pointBackgroundColor: "#36A2EB",
      pointBorderColor: "#fff",
      data: props.data,
      fill: true,
      tension: 0.4,
    },
  ],
});

const options: ChartOptions<"line"> = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: "top",
    },
    tooltip: {
      callbacks: {
        label: function (context) {
          return `${context.raw} €`;
        },
      },
    },
  },
};

watch(
  () => props.data,
  (newData) => {
    lineChartData.value = {
      labels: props.labels,
      datasets: [
        {
          label: "Purchases over time",
          backgroundColor: "rgba(54, 162, 235, 0.2)",
          borderColor: "#36A2EB",
          pointBackgroundColor: "#36A2EB",
          pointBorderColor: "#fff",
          data: newData,
          fill: true,
          tension: 0.4,
        },
      ],
    };
  },
  { immediate: true }
);
</script>

<template>
  <div class="chart-container">
    <Line :data="lineChartData" :options="options" />
  </div>
</template>

<style scoped>
.chart-container {
  width: 500px;
  height: 300px;
}
</style>
