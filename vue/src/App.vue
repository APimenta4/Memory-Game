<script setup>
import { onMounted, ref, inject } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useBoardStore } from '@/stores/board'
import Toaster from './components/ui/toast/Toaster.vue'
import { useToast } from '@/components/ui/toast/use-toast'
import axios from 'axios';
import { useErrorStore } from './stores/error'


const storeAuth = useAuthStore()
const storeBoard = useBoardStore()
const storeError = useErrorStore()
const socket = inject('socket')
const { toast } = useToast()

const notifications = ref([])

const fetchNotifications = () => {
        storeError.resetMessages()
        try {
            const response = axios.get('notifications/unread')
            console.log(response.data)
            notifications.value = response.data.unread_notifications
            return true
        }
        catch (e) {
          storeError.setErrorMessages(e.response.data.message, e.response.data.errors, e.response.status, 'Error fetching boards!')
          return false
        }
    }

onMounted( async () => {
  socket.on('notification',()=>{
    let txtTitle;
    let txtDescription;
    console.log("Notifications")
    fetchNotifications()
    console.log(notifications.value)
    notifications.value.forEach((notification)=>{
      if (notification.data === null | notification.type === null){
        return
      }
      if (notification.type.includes("TopScoreNotification")){
        // Game Record
        if (notification.data.scope === "personal"){
          txtTitle = 'Beat your personal record'
        } else {
          txtTitle = 'You Beat a Global record'
        }

        if (notification.data.score_type === "total_time"){
          txtDescription = "Top"+notification.data.position+" Time in "+notification.data.board_size+". Total Time "+notification.data.score+"s"
        } else {
          txtDescription = "Top"+notification.data.position+" Turns in "+notification.data.board_size+". Total Time "+notification.data.score+"s"
        }

      }
      else{
        // Transaction
        txtTitle = 'Transaction Successful'
        txtDescription = "Spent "+notification.data.euros+"€ for "+notification.data.brain_coins+" coins via "+notification.data.payment_type
      }
    })

    toast({
      title: txtTitle,
      description: txtDescription,
    })
  })
  storeBoard.fetchBoards()
})
</script>

<template>
  <Toaster />
  <div class="min-h-screen bg-gray-50">
    <header class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="flex items-center justify-start h-16 space-x-8">
          <RouterLink to="/"
            class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            active-class="text-blue-600 font-semibold">
            Home
          </RouterLink>
          <RouterLink to="/testers/laravel"
            class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            active-class="text-blue-600 font-semibold">
            Laravel Tester
          </RouterLink>
          <RouterLink to="/testers/websocket"
            class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            active-class="text-blue-600 font-semibold">
            WebSockets Tester
          </RouterLink>
          <RouterLink to="/singleplayer"
            class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            active-class="text-blue-600 font-semibold">
            Single Player
            </RouterLink>
          <RouterLink v-show="storeAuth.user" to="/history"
            class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            active-class="text-blue-600 font-semibold">
            My History
          </RouterLink>
          <RouterLink v-show="storeAuth.user" to="/scoreboard/personal"
            class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            active-class="text-blue-600 font-semibold">
            Personal Scoreboard
          </RouterLink>
          <RouterLink to="/scoreboard/global"
            class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            active-class="text-blue-600 font-semibold">
            Global Scoreboard
          </RouterLink>
          <RouterLink v-show="storeAuth.user" to="/transactions/buy-coins"
            class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            active-class="text-blue-600 font-semibold">
            Buy coins
          </RouterLink>
          <RouterLink v-show="storeAuth.user" to="/transactions/history"
            class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            active-class="text-blue-600 font-semibold">
            Transactions History
          </RouterLink>
        </nav>
      </div>
    </header>
    <main class="max-w-full mx-5 px-4 sm:px-6 lg:px-8">
      <RouterView />
    </main>
  </div>
</template>