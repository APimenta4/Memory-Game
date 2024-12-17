<script setup>
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
  CardFooter,
  CardDescription
} from '@/components/ui/card'
import { ref, onMounted } from 'vue'
import { useErrorStore } from '@/stores/error'
import axios from 'axios'

const storeError = useErrorStore()

const multiplayerStatistics = ref([])
// Fetch the multiplayer statistics
const fetchMultiplayerStatistics = async () => {
  storeError.resetMessages()
  try {
    const response = await axios.get(`/scoreboards/global/multiplayer/`)
    multiplayerStatistics.value = response.data
    return true
  } catch (e) {
    storeError.setErrorMessages(
      e.response.data.message,
      e.response.data.errors,
      e.response.status,
      'Error fetching statistics!'
    )
    return false
  }
}
onMounted(() => {
  fetchMultiplayerStatistics()
})

</script>
<template>
  <Card>
    <CardHeader>
      <CardTitle>Multiplayer Games</CardTitle>
      <CardDescription>Top 5 players with the most multiplayer victories</CardDescription>
    </CardHeader>
    <CardContent>
      <ul>
        <li v-for="player in multiplayerStatistics" :key="player.nickname">
          {{ player.position }}. {{ player.nickname }} - {{ player.victories }} victories 👑
        </li>
      </ul>
    </CardContent>
    <CardFooter></CardFooter>
  </Card>
</template>
