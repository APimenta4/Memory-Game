<script setup>
import { onMounted, provide, useTemplateRef, ref, inject, h } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useBoardStore } from '@/stores/board'
import Toaster from './components/ui/toast/Toaster.vue'
import { useToast } from '@/components/ui/toast/use-toast'
import { useErrorStore } from './stores/error'
import { useChatStore } from '@/stores/chat'
import axios from 'axios'

import GlobalAlertDialog from '@/components/common/GlobalAlertDialog.vue'
import GlobalInputDialog from './components/common/GlobalInputDialog.vue'
import ToastAction from './components/ui/toast/ToastAction.vue'
import BuyCoins from './components/PurchaseCoins.vue'
import UserDropdown from './components/auth/UserDropdown.vue'
import ScoreBoardDropdown from './components/ScoreBoardDropdown.vue'
import HistoryDropdown from './components/HistoryDropdown.vue'

const { toast } = useToast()
const socket = inject('socket')

const storeAuth = useAuthStore()
const storeBoard = useBoardStore()
const storeChat = useChatStore()
const storeError = useErrorStore()

const alertDialog = useTemplateRef('alert-dialog')
provide('alertDialog', alertDialog)
const inputDialog = useTemplateRef('input-dialog')
provide('inputDialog', inputDialog)


const notifications = ref([])
const fetchNotifications = async () => {
  storeError.resetMessages()
  try {
    const response = await axios.get('notifications/unread')
    notifications.value = response.data.unread_notifications
    makeNotification()
    return true
  } catch (e) {
    console.log(e)
    // storeError.setErrorMessages(e.response.data.message, e.response.data.errors, e.response.status, 'Error fetching boards!')
    return false
  }
}

const makeNotification = () => {
  let txtTitle
  let txtDescription
  notifications.value.forEach((notification) => {
    console.log("notification")
    console.log(notification)
    console.log("notification.value")
    console.log(notification.value)
    console.log("notification.data")
    console.log(notification.data)
    if ((notification.data === null) | (notification.type === null)) {
      return
    }
    if (notification.type.includes('TopScoreNotification')) {
      // Game Record
        console.log("record")
      if (notification.data.scope === 'personal') {
        console.log("top personal")
        txtTitle = 'Beat your personal record'
      } else {
        console.log("top global")
        txtTitle = 'You Beat a Global record'
      }

      if (notification.data.score_type === 'total_time') {
        console.log("top time")
        txtDescription =
          'Top' +
          notification.data.position +
          ' Time in ' +
          notification.data.board_size +
          '. Total Time ' +
          notification.data.score +
          's'
      } else {
      console.log("top turns")

        txtDescription =
          'Top' +
          notification.data.position +
          ' Turns in ' +
          notification.data.board_size +
          '. Total turns ' +
          notification.data.score
      }
    } else {
      // Transaction
      console.log("transaction")
      console.log(notification.data.type)
      if (notification.data.type === 'B') {
        txtTitle = 'New User Bonus'
        txtDescription = 'You Won ' + notification.data.brain_coins + ' coins'
      } else if(notification.data.type === 'I') {
        txtTitle = 'You Won!'
        txtDescription = 'You won ' + notification.data.brain_coins + ' brain coins'
      }
      
      
      else {
        txtTitle = 'Transaction Successful!'
        txtDescription = 'You spent '+ notification.data.euros +'€ for ' + notification.data.brain_coins + ' brain coins'
      }
    }

    console.log(txtTitle)
    console.log(txtDescription)
    // so they dont stack
    setTimeout(()=> axios.patch(`notifications/${notification.id}/read`), 5000)
    toast({
      title: txtTitle,
      description: txtDescription,
      action: h(
        ToastAction,
        {
          altText: 'Mark as Read',
          onclick: () => {
            axios.patch(`notifications/${notification.id}/read`)
          }
        },
        {
          default: () => 'Mark as Read'
        }
      )
    })

  })
}
socket.on('notification', () => {
  fetchNotifications()
})

let userDestination = null
const handleMessageFromInputDialog = (message) => {
  storeChat.sendPrivateMessageToUser(userDestination, message)
}
socket.on('privateMessage', (messageObj) => {
  userDestination = messageObj.user
  inputDialog.value.open(
    handleMessageFromInputDialog,
    'Message from ' + messageObj.user.name,
    `This is a private message sent by ${messageObj?.user?.name}!`,
    'Reply Message',
    '',
    'Close',
    'Reply',
    messageObj.message
  )
})

onMounted(async () => {
  storeBoard.fetchBoards()
})
</script>

<template>
  <Toaster />
  <GlobalAlertDialog ref="alert-dialog"></GlobalAlertDialog>
  <GlobalInputDialog ref="input-dialog"></GlobalInputDialog>
  <div class="min-h-screen bg-gray-50">
    <header class="bg-white shadow-sm flex">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="flex flex-wrap items-center justify-center p-2 space-x-0 gap-2">
          <RouterLink
            to="/"
            class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            active-class="bg-gray-200"
          >
            Home
          </RouterLink>
          <RouterLink
            v-show="storeAuth.user?.type != 'A'"
            to="/singleplayer"
            class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            active-class="bg-gray-200"
          >
            Single Player
          </RouterLink>
          <RouterLink
            to="/multiplayer"
            class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            active-class="bg-gray-200"
            >
            MultiPlayer
          </RouterLink>
          <ScoreBoardDropdown v-if="(storeAuth.user && storeAuth.user.type != 'A') || !storeAuth.user"/>
          <HistoryDropdown v-if="storeAuth.user"/>
        <RouterLink
          v-show="storeAuth.user?.type == 'A'"
          to="/users"
          class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
          active-class="bg-gray-200"
        >
          User List
        </RouterLink>
          <RouterLink
            to="/statistics/personal"
            class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            active-class="bg-gray-200"
          >
            Statistics
          </RouterLink>
          <RouterLink
            v-if="!storeAuth.user"
            to="/login"
            class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            active-class="bg-gray-200"
          >
            Login
          </RouterLink>
          <RouterLink
            v-if="!storeAuth.user"
            to="/register"
            class="text-gray-900 hover:text-blue-600  px-3 py-2 rounded-md text-sm font-medium transition-colors"
            active-class="bg-gray-200"
          >
            Register
          </RouterLink>
          <BuyCoins v-if="storeAuth.user && storeAuth.user.type !=='A'"/>
          <div class="flex flex-wrap items-center justify-center p-2 space-x-0 gap-2">
            <div v-if="storeAuth.user && storeAuth.user.type !=='A'" style="display: flex; align-items: center">
              <span style="font-size: 24px; margin-right: 8px">🧠</span>
              <span style="font-size: 18px">{{ storeAuth.user.brain_coins_balance }}</span>
            </div>


            <UserDropdown v-if="storeAuth.user"/>
          </div>
        </nav>
      </div>
    </header>
    <main class="max-w-full mx-5 px-4 sm:px-6 lg:px-8">
      <RouterView />
    </main>
  </div>
</template>
