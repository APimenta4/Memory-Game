<script setup>
import { useRouter } from 'vue-router'
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

const { toast } = useToast()
const socket = inject('socket')
const router = useRouter()

const storeAuth = useAuthStore()
const storeBoard = useBoardStore()
const storeChat = useChatStore()
const storeError = useErrorStore()

const isSubMenuVisible = ref(false) // Ref to manage submenu visibility

const alertDialog = useTemplateRef('alert-dialog')
provide('alertDialog', alertDialog)
const inputDialog = useTemplateRef('input-dialog')
provide('inputDialog', inputDialog)

const logout = async () => {
  await storeAuth.logout()
  isSubMenuVisible.value = false
}

const toggleSubMenu = () => {
  isSubMenuVisible.value = !isSubMenuVisible.value
}

const seeProfile = () => {
  router.push({ name: 'profile' })
  isSubMenuVisible.value = false
}

const editProfile = () => {
  router.push({ name: 'profileEdit' })
  isSubMenuVisible.value = false
}

const notifications = ref([])
const fetchNotifications = async () => {
  storeError.resetMessages()
  try {
    const response = await axios.get('notifications/unread')
    notifications.value = response.data.unread_notifications
    console.log(notifications.value)
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
    if ((notification.data === null) | (notification.type === null)) {
      return
    }
    if (notification.type.includes('TopScoreNotification')) {
      // Game Record
      if (notification.data.scope === 'personal') {
        txtTitle = 'Beat your personal record'
      } else {
        txtTitle = 'You Beat a Global record'
      }

      if (notification.data.score_type === 'total_time') {
        txtDescription =
          'Top' +
          notification.data.position +
          ' Time in ' +
          notification.data.board_size +
          '. Total Time ' +
          notification.data.score +
          's'
      } else {
        txtDescription =
          'Top' +
          notification.data.position +
          ' Turns in ' +
          notification.data.board_size +
          '. Total Time ' +
          notification.data.score +
          's'
      }
    } else {
      // Transaction
      if (notification.data.type === 'B') {
        txtTitle = 'New User Bonus'
        txtDescription = 'You Won ' + notification.data.brain_coins + ' coins'
      } else if (notification.data.type === 'I') {
        txtTitle = 'More Brains!'
        txtDescription = 'You Won ' + notification.data.brain_coins + ' coins'
      } else {
        txtTitle = 'Transaction Successful'
        txtDescription =
          'Spent ' +
          notification.data.euros +
          '€ for ' +
          notification.data.brain_coins +
          ' coins via ' +
          notification.data.payment_type
      }
    }
    console.log(txtTitle)
    console.log(txtDescription)
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
  console.log('notifications')
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
    <header class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="flex items-center justify-start h-16 space-x-8">
          <RouterLink
            to="/"
            class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            active-class="text-blue-600 font-semibold"
          >
            Home
          </RouterLink>
          <RouterLink
            to="/testers/websocket"
            class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            active-class="text-blue-600 font-semibold"
          >
            WebSockets Tester
          </RouterLink>
          <RouterLink
            v-show="storeAuth.user?.type != 'A'"
            to="/singleplayer"
            class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            active-class="text-blue-600 font-semibold"
          >
            Single Player
          </RouterLink>
          <RouterLink
            to="/multiplayer"
            class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            active-class="text-blue-600 font-semibold"
          >
            Multi Player
          </RouterLink>
          <RouterLink
            v-show="storeAuth.user"
            to="/history"
            class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            active-class="text-blue-600 font-semibold"
          >
            <span v-if="storeAuth.user?.type != 'A'">My </span>Game History
          </RouterLink>
          <RouterLink
            v-show="storeAuth.user?.type == 'A'"
            to="/users"
            class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            active-class="text-blue-600 font-semibold"
          >
            User List
          </RouterLink>
          <RouterLink
            v-show="storeAuth.user && storeAuth.user.type != 'A'"
            to="/scoreboard/personal"
            class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            active-class="text-blue-600 font-semibold"
          >
            Personal Scoreboard
          </RouterLink>
          <RouterLink
            to="/scoreboard/global"
            class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            active-class="text-blue-600 font-semibold"
          >
            Global Scoreboard
          </RouterLink>
          <RouterLink
            v-show="storeAuth.user && storeAuth.user.type != 'A'"
            to="/transactions/buy-coins"
            class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            active-class="text-blue-600 font-semibold"
          >
            Buy coins
          </RouterLink>
          <RouterLink
            v-show="storeAuth.user"
            to="/transactions/history"
            class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            active-class="text-blue-600 font-semibold"
          >
            <span v-if="storeAuth.user?.type != 'A'">My </span>Transaction History
          </RouterLink>
                  
          <RouterLink 
            v-if="storeAuth.user?.type === 'A'" 
            to="/statistics/admin"
            class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            active-class="text-blue-600 font-semibold"
          >
            Statistics
          </RouterLink>
          <RouterLink 
            v-else-if="storeAuth.user" 
            to="/statistics/personal"
            class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            active-class="text-blue-600 font-semibold">
            Statistics
          </RouterLink>
          <RouterLink 
            v-else 
            to="/statistics"
            class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            active-class="text-blue-600 font-semibold">
            Statistics
          </RouterLink>


          <RouterLink v-show="!storeAuth.user" to="/login"
            class="text-white bg-blue-600 hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            active-class="bg-blue-700"
          >
            Login
          </RouterLink>
          <RouterLink
            v-show="!storeAuth.user"
            to="/register"
            class="text-white bg-blue-600 hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            active-class="bg-blue-700"
          >
            Register
          </RouterLink>


          <div v-if="storeAuth.user && storeAuth.user?.type!='A'" style="display: flex; align-items: center">
            <span style="font-size: 24px; margin-right: 8px">🧠</span>
            <span style="font-size: 18px">{{ storeAuth.user.brain_coins_balance }}</span>
          </div>

          <BuyCoins v-if="storeAuth.user && storeAuth.user?.type!='A'"> </BuyCoins>

          <!-- Profile Image -->
          <img
            v-if="storeAuth.user"
            @click="toggleSubMenu"
            class="w-14 h-14 min-w-14 min-h-14 object-cover rounded-full cursor-pointer"
            :src="storeAuth.userPhotoUrl"
            alt="Profile Picture"
          />

          <!-- Submenu (Conditional Rendering) -->
          <div
            v-if="isSubMenuVisible"
            class="absolute top-16 right-0 mt-2 w-48 bg-white shadow-lg rounded-lg border z-50"
          >
            <ul class="space-y-2 p-2 text-gray-700">
              <li>
                <button
                  @click="editProfile"
                  class="block px-4 py-2 w-full text-left hover:bg-gray-100 rounded-md"
                >
                  Edit Profile
                </button>
              </li>
              <li>
                <button
                  @click="seeProfile"
                  class="block px-4 py-2 w-full text-left hover:bg-gray-100 rounded-md"
                >
                  See Profile
                </button>
              </li>
              <li>
                <button
                  @click="logout"
                  class="block px-4 py-2 w-full text-left hover:bg-gray-100 rounded-md"
                >
                  Logout
                </button>
              </li>
            </ul>
          </div>
        </nav>
      </div>
    </header>
    <main class="max-w-full mx-5 px-4 sm:px-6 lg:px-8">
      <RouterView />
    </main>
  </div>
</template>

<style scoped>
/* Additional styling for submenu */
nav {
  position: relative; /* Ensure submenu is positioned correctly relative to the navbar */
}

.submenu {
  position: absolute;
  top: 16px; /* Adjust the position below the profile image */
  right: 0;
  background-color: white;
  border: 1px solid #ddd;
  box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
  border-radius: 8px;
  z-index: 100; /* Ensure the submenu is above other content */
}

.submenu a,
.submenu button {
  padding: 8px 16px;
  display: block;
  width: 100%;
  text-align: left;
  color: #333;
}

.submenu a:hover,
.submenu button:hover {
  background-color: #f3f4f6;
}
</style>
