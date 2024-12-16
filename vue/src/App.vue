<script setup>
import { onMounted, provide, useTemplateRef, inject } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useBoardStore } from '@/stores/board'
import Toaster from './components/ui/toast/Toaster.vue'
import { useChatStore } from '@/stores/chat'

import GlobalAlertDialog from '@/components/common/GlobalAlertDialog.vue'
import GlobalInputDialog from './components/common/GlobalInputDialog.vue'

const storeAuth = useAuthStore()
const storeBoard = useBoardStore()
const storeChat = useChatStore()
const socket = inject('socket')

const alertDialog = useTemplateRef('alert-dialog')
provide('alertDialog', alertDialog)
const inputDialog = useTemplateRef('input-dialog')
provide('inputDialog', inputDialog)

let userDestination = null
socket.on('privateMessage', (messageObj) => {
    userDestination = messageObj.user   
    inputDialog.value.open(
        handleMessageFromInputDialog,
        'Message from ' + messageObj.user.name,
        `This is a private message sent by ${messageObj?.user?.name}!`,
        'Reply Message', '',
        'Close', 'Reply',
        messageObj.message
    )
})
const handleMessageFromInputDialog = (message) => {
    storeChat.sendPrivateMessageToUser(userDestination, message)
}

onMounted(() => {
  storeBoard.fetchBoards()
})
</script>

<template>
  <Toaster />
  <GlobalAlertDialog ref="alert-dialog"></GlobalAlertDialog>
  <GlobalInputDialog ref="input-dialog"></GlobalInputDialog>
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
          <RouterLink to="/multiplayer"
            class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            active-class="text-blue-600 font-semibold">
            Multi Player
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
          
          <RouterLink to="/statistics"
            class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            active-class="text-blue-600 font-semibold">
            Statistics
          </RouterLink>
         
        </nav>
      </div>
    </header>
    <main class="max-w-full mx-5 px-4 sm:px-6 lg:px-8">
      <RouterView />
    </main>
  </div>
</template>