

<script setup>
import { Card, CardHeader, CardTitle, CardContent, CardFooter} from "@/components/ui/card"
import { useAuthStore } from "@/stores/auth";

const props = defineProps({
  time: {
    type: String,
    required: true,
  },
  turns: {
    type: Number,
    required: true,
  },
  pairsFound: {
    type: Number,
    required: true,
  },
  totalPairs: {
    type: Number,
    required: true,
  },
  isGameOver: {
    type: Boolean,
    required: true,
  },
})

const storeAuth = useAuthStore()
const emit = defineEmits(['restart'])

const restart = ()=>{
  emit('restart')
}
</script>

<template>
  <Card 
    :class="{
      'bg-green-100' : isGameOver
    }" 
  class="h-fit">
    <CardHeader>
      <CardTitle>Game Status: 
        <span class="text-green-700" v-if="isGameOver"><b>Done</b></span>
        <span class="text-gray-400" v-if="time!=='0.0' && time && !isGameOver"><b>In Progress</b></span>
        <span class="text-gray-400" v-if="time==='0.0' || !time">Ready</span>
      </CardTitle>
    </CardHeader>

    <CardContent>
      <table class="min-w-full table-auto">
        <tbody>
          <tr>
            <td>Time:</td>
            <td> {{ time }}s</td>
          </tr>
          <tr>
            <td>Turns:</td>
            <td> {{ turns }}</td>
          </tr>
          <tr>
            <td>Pairs: </td>
            <td> {{ pairsFound }}/{{ totalPairs }}</td>
          </tr>
        </tbody>
      </table>
    </CardContent>
    <CardFooter className="flex flex-col items-center mb-5">
      <span 
        v-show="!storeAuth.user"
        class="text-gray-400 bold"
        :class="{'text-gray-700 bold': isGameOver}"
      >Login to be able to send your records</span>
      <div v-if="isGameOver" class="text-center mt-6">
        <p class="text-green-600 font-bold">🎉 Great memory! 🎉</p>
      </div>
      <button @click="restart"
          :class="{
            'bg-gray-500': isGameOver,
            'bg-gray-400': !isGameOver
          }"
          class="mt-4 text-white px-6 py-2 rounded shadow hover:bg-gray-600 transition"
        >
          Restart Game
        </button>
      </CardFooter>
  </Card>
</template>
