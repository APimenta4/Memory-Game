<script setup>
import { ref, onMounted, watch } from 'vue'
import { useErrorStore } from '@/stores/error'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle
} from '@/components/ui/card'
import avatarNoneAssetURL from '@/assets/avatar-none.png'
import axios from 'axios'

const storeError = useErrorStore()

const allUsers = ref([])
const filteredUsers = ref([])

const username = ref('')

const fetchUsers = async () => {
  storeError.resetMessages()
  try {
    const response = await axios.get(`/users`)
    allUsers.value = response.data.data
    filteredUsers.value = allUsers.value
  } catch (e) {
    storeError.setErrorMessages(
      e.response.data.message,
      e.response.data.errors,
      e.response.status,
      'Error fetching users!'
    )
  }
}

onMounted(() => {
  fetchUsers()
})

watch(username, (newVal) => {
  const searchTerm = newVal.toLowerCase()
  filteredUsers.value = allUsers.value.filter((user) =>
    user.name.toLowerCase().includes(searchTerm)
  )
})

function toggleBlock(user) {
  user.blocked = !user.blocked
  // Optionally, make an API call to persist the change
}

// Example remove user function
function removeUser(user) {
  filteredUsers.value = filteredUsers.value.filter((u) => u.id !== user.id)
  // Optionally, make an API call to remove the user
}
</script>

<template>
  <div class="mb-5 mt-5">
    <label for="username" class="block text-sm font-medium text-gray-700">Search by Name:</label>
    <Input v-model="username" placeholder="Name..." />
  </div>

  <div class="users-list grid grid-cols-1 gap-4 md:grid-cols-4 lg:grid-cols-5 mx-10">
    <Card
      v-for="user in filteredUsers"
      :key="user.id"
      class="shadow-md h-[22rem] w-[16rem] relative flex flex-col"
    >
      <CardHeader class="flex flex-col items-center justify-center flex-shrink-0 space-y-2">
        <div class="w-[7rem] h-[7rem] overflow-hidden rounded-full flex-shrink-0">
          <img
            :src="
              user.photo_filename
                ? axios.defaults.baseURL.replace('/api', `/storage/photos/${user.photo_filename}`)
                : avatarNoneAssetURL
            "
            alt="User Photo"
            class="object-cover w-full h-full"
          />
        </div>
        <CardTitle class="text-base font-bold text-center w-[80%] line-clamp-2" :title="user.name">
          {{ user.name }}
        </CardTitle>
        <p class="text-xl font-bold text-muted-foreground text-center w-[80%] truncate">
          {{ user.nickname }}
          <span v-if="user.type === 'A'" class="text-orange-500">[Admin]</span>
        </p>
      </CardHeader>

      <CardContent class="flex-grow text-center">
        <CardDescription>{{ user.email }}</CardDescription>
      </CardContent>

      <CardFooter class="flex justify-between absolute bottom-0 left-0 right-0 p-3">
        <Button :class="user.blocked ? 'bg-green-500' : 'bg-orange-500'" @click="toggleBlock(user)">
          {{ user.blocked ? 'Unblock' : 'Block' }}
        </Button>
        <Button class="bg-red-500" @click="removeUser(user)">Remove</Button>
      </CardFooter>
    </Card>
  </div>
</template>
