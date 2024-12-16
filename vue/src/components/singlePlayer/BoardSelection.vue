<script setup>
import { h } from 'vue';
import { Card, CardTitle } from '@/components/ui/card'
import { useAuthStore } from '@/stores/auth'
import { useGameStore } from '@/stores/game'
import { toast } from '../ui/toast';
import ToastAction from '../ui/toast/ToastAction.vue';
import router from '@/router';
import CardDescription from '../ui/card/CardDescription.vue';
import CardFooter from '../ui/card/CardFooter.vue';
// Data to be fetched
const props = defineProps({
  board: {
    type: Object,
    required: true
  }
})

const gameStore = useGameStore()
const authStore = useAuthStore()

const setBoard = () => {
  gameStore.board = props.board
}
const showToast = () => {
  toast({
      title: 'You need Coins to play this boards!',
      description:
        "Log in to be able to use in coins.",
      action: h(
        ToastAction,
        {
          altText: 'Log in',
          onclick: () => {
            router.push('/testers/laravel')
          }
        },
        {
          default: () => 'Log In'
        }
      )
    })
}
</script>

<template>
  <!-- RouterLink for authenticated users -->
  <RouterLink
    v-if="(authStore.user && authStore.user.brain_coins_balance) || (board.board_cols===3 && board.board_rows===4)"
    to="/singleplayer/game"
    @click="setBoard"
    class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
    active-class="text-blue-600 font-semibold"
  >
    <Card
      class="w-96 h-64 flex flex-col justify-center items-center p-6 hover:shadow-lg cursor-pointer"
    >
      <CardTitle class="my-5 text-4xl"> {{ board.board_cols }}x{{ board.board_rows }} </CardTitle>
      <CardDescription class="text-2xl" v-if="(board.board_cols===3 && board.board_rows===4)">0 🪙</CardDescription>
      <CardDescription class="text-2xl"v-else>1 🪙</CardDescription>
    </Card>
  </RouterLink>

  <!-- RouterLink for unauthenticated users -->
  <RouterLink
    v-else
    to=""
    @click="showToast"
    class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
    active-class="text-blue-600 font-semibold"
  >
    <Card
      class="w-96 h-64 flex flex-col justify-center items-center p-6 hover:shadow-lg  bg-gray-200 text-gray-400 cursor-not-allowed"
    >
    <CardTitle class="my-5 text-4xl">
      {{ board.board_cols }}x{{ board.board_rows }}
    </CardTitle>
    <CardDescription class="text-gray-400 text-2xl" v-if="!(board.board_cols===3 && board.board_rows===4)">1 🪙</CardDescription>
    </Card>
  </RouterLink>
</template>
