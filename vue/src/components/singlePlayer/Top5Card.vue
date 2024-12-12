
<script setup>
import { useErrorStore } from '@/stores/error';
import { onMounted, ref, watch } from 'vue';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter} from "@/components/ui/card"
import axios from 'axios';
import { useGameStore } from '@/stores/game';

const props = defineProps({
  board: {
    type: Object,
    required: true,
  },
})

const storeGame = useGameStore()

watch(
  () => storeGame.reloadRequest,
  ()=>{
    fetchScoreboardTurns()
    fetchScoreboardTime()
  },
  { deep: true }
)

const storeError = useErrorStore()
const scoreboardTurns = ref([])
const scoreboardTime = ref([])

const fetchScoreboardTurns = async () => {
  storeError.resetMessages();
  try {
    const response = await axios.get(`/scoreboards/global/singleplayer`, {
      params: {
        board_id: props.board.id,
        scoreboard_type: "turns",
      },
    });
    scoreboardTurns.value = response.data.data
    return true;
  } catch (e) {
    storeError.setErrorMessages(e.response.data.message, e.response.data.errors, e.response.status, 'Error fetching games!');
    return false;
  }
};

const fetchScoreboardTime = async () => {
  storeError.resetMessages();
  try {
    const response = await axios.get(`/scoreboards/global/singleplayer`, {
      params: {
        board_id: props.board.id,
        scoreboard_type: "time",
      },
    });
    scoreboardTime.value = response.data.data
    return true;
  } catch (e) {
    storeError.setErrorMessages(e.response.data.message, e.response.data.errors, e.response.status, 'Error fetching games!');
    return false;
  }
};

onMounted(()=>{
  fetchScoreboardTurns()
  fetchScoreboardTime()
})

</script>

<template>
  <Card class="h-fit">
    <CardHeader>
      <CardTitle>Top 5 Turns {{props.board.board_cols}}x{{props.board.board_rows}}</CardTitle>
      <CardDescription>Players with the least turns to finish</CardDescription>
    </CardHeader>
    <CardContent>
      <table class="min-w-full">
        <thead>
          <tr class="text-left">
            <th class="p-1">Pos</th>
            <th class="p-1">Turns</th>
            <th class="p-1">Player</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(game, index) in scoreboardTurns" :key="game.creator_nickname">
            <td v-show="index === 0" class="p-1">🏆</td>
            <td v-show="index === 1" class="p-1">🥈</td>
            <td v-show="index === 2" class="p-1">🥉</td>
            <td v-show="index == 3" class="p-1">🐋</td>
            <td v-show="index === 4" class="p-1">🐟</td>
            <td class="p-1">{{ game.total_turns_winner }}</td>
            <td class="p-1">{{ game.creator_nickname }}</td>
          </tr>
        </tbody>
      </table>
    </CardContent>
    <CardHeader>
      <CardTitle>Top 5 Time {{props.board.board_cols}}x{{props.board.board_rows}}</CardTitle>
      <CardDescription>Players with the shortest time to finish</CardDescription>
    </CardHeader>

    <CardContent>
      <table class="min-w-full">
        <thead>
          <tr class="text-left">
            <th class="p-1">Pos</th>
            <th class="p-1">Time</th>
            <th class="p-1">Player</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(game, index) in scoreboardTime" :key="game.creator_nickname">
            <td v-show="index === 0" class="p-1">🏆</td>
            <td v-show="index === 1" class="p-1">🥈</td>
            <td v-show="index === 2" class="p-1">🥉</td>
            <td v-show="index == 3" class="p-1">🐟</td>
            <td v-show="index === 4" class="p-1">🐋</td>
            <td class="p-1">{{ game.total_time }}</td>
            <td class="p-1">{{ game.creator_nickname }}</td>
          </tr>
        </tbody>
      </table>
    </CardContent>
    <CardFooter></CardFooter>
  </Card>
</template>
