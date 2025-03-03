<script setup>
import { ref, inject } from 'vue'
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { ScrollArea } from '@/components/ui/scroll-area';
import { useChatStore } from '@/stores/chat'
import { useAuthStore } from '@/stores/auth'
import axios from 'axios'
import avatarNoneAssetURL from '@/assets/avatar-none.png'

const storeChat = useChatStore()
const storeAuth = useAuthStore()


const inputDialog = inject('inputDialog')

const message = ref('')

const canSendMessageToUser = (user) => {
    return user && storeAuth.user && user.id !== storeAuth.user.id
}

const sendMessageToChat = () => {
    storeChat.sendMessageToChat(message.value)
    message.value = ''
}

let userDestination = null
const sendPrivateMessageToUser = (user) => {
    userDestination = null
    if (canSendMessageToUser(user)) {
        userDestination = user
        inputDialog.value.open(
            handleMessageFromInputDialog,
            'Message to ' + user.name,
            `Only ${user.name} will receive this message!`,
            'Message', '',
            'Close', 'Send',
            ''
        )
    }
}

const apiDomain = import.meta.env.VITE_API_DOMAIN
const userPhotoUrl = (user) => {
  const photoFile = user?.photo_filename
  if (photoFile) {
    return `http://${apiDomain}/storage/photos/${photoFile}`
  }
  return avatarNoneAssetURL
}


const handleMessageFromInputDialog = (message) => {
    storeChat.sendPrivateMessageToUser(userDestination, message)
}
</script>

<template>
    <Card class="my-8 py-2 px-1 h-full flex flex-col">
        <CardHeader class="pb-6">
            <CardTitle>Chat</CardTitle>
            <CardDescription>
                Here you may chat with everyone else on the app. Say hi!<br>
                <em>Click on the user name to send them a private message.</em>
            </CardDescription>
        </CardHeader>
        <CardContent class="p-4 flex-grow overflow-hidden">
            <ScrollArea class="h-full">
                <div class="divide-y divide-solid divide-gray-200">
                    <div v-if="storeChat.totalMessages > 0">
                        <div v-for="messageObj in storeChat.messages" :key="messageObj" class="flex">
                            <img class="w-10 h-10 rounded-lg mr-4" :src="userPhotoUrl(messageObj.user)" alt="User Avatar" />
                            <div class="flex flex-col grow pb-6">
                                <div class="text-xs text-gray-500">
                                    <span :class="{
                                        'hover:text-green-300': canSendMessageToUser(messageObj.user),
                                        'hover:cursor-pointer' :canSendMessageToUser(messageObj.user)
                                    }" @click="sendPrivateMessageToUser(messageObj.user)">{{ messageObj.user?.nickname ?? 'Anonymous' }}</span>
                                </div>
                                <div class="mt-1 text-base grow leading-6">
                                     {{ messageObj.message }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else>
                        <h2 class="text-xl">No messages!</h2>
                    </div>
                </div>
            </ScrollArea>
        </CardContent>
        <div class="p-4">
            <Label for="inputMessage" class="pt-4">
                Press enter to send message:
            </Label>
            <Input id="inputMessage" v-model="message" @keydown.enter="sendMessageToChat"/>
        </div>
    </Card>
</template>