<script setup>
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card'
import { useGamesStore } from '@/stores/games'
import { ref } from 'vue'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle
} from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import router from '@/router'

const storeGames = useGamesStore()

const isDialogOpen = ref(false)
const openDialog = () => {
  isDialogOpen.value = true
}
const closeDialog = () => {
  isDialogOpen.value = false
}

const abandonGame = async () => {
  if (storeGames.currentGame.status === 0) {
    await storeGames.quit(storeGames.currentGame)
  }
  isDialogOpen.value = false
  setTimeout(() => {
    storeGames.close(storeGames.currentGame);
    storeGames.isClosed = true;
  }, 1000);
  router.back()
}
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>
        Game Status:
        <br />
        <span> {{ storeGames.gameStatus }} </span>
      </CardTitle>
    </CardHeader>
    <CardContent>
      <table class="min-w-full divide-y divide-gray-200">
        <thead>
          <tr>
            <th
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Player
            </th>
            <th
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Pairs Found
            </th>
            <th
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Total Turns
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr :class="{ 'text-green-500': storeGames.myPlayerNumber === 1 }">
            <td class="px-6 py-4 whitespace-nowrap">{{ storeGames.currentGame.player1Nickname }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ storeGames.currentGame.player1Score }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ storeGames.currentGame.player1Turns }}</td>
          </tr>
          <tr :class="{ 'text-green-500': storeGames.myPlayerNumber === 2 }">
            <td class="px-6 py-4 whitespace-nowrap">{{ storeGames.currentGame.player2Nickname }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ storeGames.currentGame.player2Score }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ storeGames.currentGame.player2Turns }}</td>
          </tr>
        </tbody>
      </table>

      <Button v-if="storeGames.currentGame.gameStatus == 0" @click="openDialog" class="bg-red-500 mt-2 mr-3"> Quit Game </Button>
      <Button v-else @click="abandonGame" class="bg-gray-200 text-gray-700 mt-2">Return to lobby</Button>

      <Dialog v-model:open="isDialogOpen">
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Are you absolutely sure?</DialogTitle>
            <DialogDescription>
              Abandoning the game will result in a loss. Are you sure you want to quit?

              <div class="flex justify-end mt-4 space-x-2">
                <Button @click="closeDialog">Cancel</Button>
                <Button class="bg-red-600" @click="abandonGame">Abandon Game</Button>
              </div>
            </DialogDescription>
          </DialogHeader>
        </DialogContent>
      </Dialog>
    </CardContent>
  </Card>
</template>
