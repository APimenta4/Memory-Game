

<script setup>
import { Card, CardHeader, CardTitle, CardContent, CardFooter} from "@/components/ui/card"
import router from "@/router";
import { useAuthStore } from "@/stores/auth";
import CardDescription from "../ui/card/CardDescription.vue";
import { toast } from '@/components/ui/toast'
import { h } from "vue";
import BuyCoins from '@/components/PurchaseCoins.vue'
import { useGameStore } from "@/stores/game";
import { DialogTrigger } from '@/components/ui/dialog'

const storeGame = useGameStore()

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

const authStore = useAuthStore()
const emit = defineEmits(['restart'])

const restart = ()=>{
  console.log("user")
  console.log(authStore.user)
  console.log("boardid"+storeGame.board.id)
  if (authStore.user && authStore.user.brain_coins_balance>0 || storeGame.board.id===1){
    emit('restart')
  }
  else{
    toast({
      title: 'You need Coins to play this boards!',
      description:
        "Go Buy some coins.",
      action: h(
        BuyCoins,
      )
    })
  }
}

const showToast = ()=>{

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
      <div v-if="isGameOver" class="text-center mt-6">
        <p class="text-green-600 font-bold">🎉 Great memory! 🎉</p>
      </div>
      <CardDescription class="pt-4" v-if="totalPairs!=6">
        The brain coin will be used when you flip the first card
      </CardDescription>
      <DialogTrigger>
        <button 
        @click="restart"
        :class="{
          'bg-gray-500': isGameOver,
          'bg-gray-400': !isGameOver
        }"
        class="mt-4 text-white px-6 py-2 rounded shadow bg-gray-400 hover:bg-gray-600 transition"
      >
      Restart Game <span v-if="(isGameOver || time!=='0.0' && time && !isGameOver) && totalPairs!=6"> 1🧠</span> 
      </button>
      </DialogTrigger>
        <button 
        @click="router.back"
        :class="{
          'bg-gray-500': isGameOver,
          'bg-gray-400': !isGameOver
        }"
        class="mt-4 text-white px-6 py-2 rounded shadow bg-gray-400 hover:bg-gray-600 transition"
      >
        Return
      </button>
    </CardFooter>
  </Card>
</template>