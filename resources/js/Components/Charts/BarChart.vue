<script setup>
import { ref } from 'vue'
import { Bar } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend
} from 'chart.js'

ChartJS.register(
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend
)

const props = defineProps({
  chartData: {
    type: Object,
    required: true
  },
  options: {
    type: Object,
    default: () => ({})
  }
})

const chartOptions = ref({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: true,
      position: 'top',
      labels: {
        color: 'rgb(156, 163, 175)',
        font: {
          size: 11,
          weight: 'bold'
        },
        padding: 12,
        usePointStyle: true
      }
    },
    tooltip: {
      backgroundColor: 'rgba(0, 0, 0, 0.8)',
      padding: 12,
      titleFont: {
        size: 13,
        weight: 'bold'
      },
      bodyFont: {
        size: 12
      },
      borderColor: 'rgba(255, 255, 255, 0.1)',
      borderWidth: 1
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      grid: {
        color: 'rgba(156, 163, 175, 0.1)',
        drawBorder: false
      },
      ticks: {
        color: 'rgb(156, 163, 175)',
        font: {
          size: 10
        }
      }
    },
    x: {
      grid: {
        display: false
      },
      ticks: {
        color: 'rgb(156, 163, 175)',
        font: {
          size: 10
        }
      }
    }
  },
  ...props.options
})
</script>

<template>
  <Bar :data="chartData" :options="chartOptions" />
</template>
