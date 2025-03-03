<script setup>
import { ref, onMounted, watch } from 'vue'
import { useErrorStore } from '@/stores/error'
import { useAuthStore } from '@/stores/auth'
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
import { useRouter } from 'vue-router'

const storeError = useErrorStore()
const storeAuth = useAuthStore()
const router = useRouter()

const allUsers = ref([])
const filteredUsers = ref([])

const username = ref('')

const defaultAdminCard = {
  id: 'default-admin',
  photo_filename: null,
  name: 'Create Administrator Account',
  nickname: '',
  email: '',
  type: 'A', 
  blocked: false
}

const fetchUsers = async () => {
  storeError.resetMessages()
  try {
    const response = await axios.get(`/users`)
    allUsers.value = response.data.data
    filteredUsers.value = [defaultAdminCard, ...allUsers.value] // Ensure default card is first
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
  if (searchTerm) {
    filteredUsers.value = allUsers.value.filter((user) =>
      user.name.toLowerCase().includes(searchTerm)
    )
  } else {
    filteredUsers.value = [defaultAdminCard, ...allUsers.value] // Add the default card back when input is erased
  }
})

function toggleBlock(user) {
  const url = `/users/${user.id}/${user.blocked ? 'unblock' : 'block'}`;
  axios.patch(url)
    .then(response => {
      user.blocked = !user.blocked;
    })
    .catch(e => {
      if (e.response.status === 400) {
        user.blocked = !user.blocked;
      } else if (e.response.status === 404) {
        console.log("404");
        allUsers.value = allUsers.value.filter(u => u.id !== user.id);
        filteredUsers.value = filteredUsers.value.filter(u => u.id !== user.id);
        storeError.setErrorMessages(
          'This user has already been deleted',
          'This user has already been deleted',
          'This user has already been deleted',
          `Error ${user.blocked ? 'unblocking' : 'blocking'} user!`
        );
      } else {
        storeError.setErrorMessages(
          e.response.data.message,
          e.response.data.errors,
          e.response.status,
          `Error ${user.blocked ? 'unblocking' : 'blocking'} user!`
        );
      }
    });
}

function removeUser(user) {
  const url = `/users/${user.id}`;
  axios.delete(url)
    .then(response => {
      allUsers.value = allUsers.value.filter(u => u.id !== user.id);
      filteredUsers.value = filteredUsers.value.filter(u => u.id !== user.id);
    })
    .catch(e => {
      console.log(e.response.data);
      if (e.response.status === 404) {
        allUsers.value = allUsers.value.filter(u => u.id !== user.id);
        filteredUsers.value = filteredUsers.value.filter(u => u.id !== user.id);
        storeError.setErrorMessages(
          'This user has already been deleted',
          'This user has already been deleted',
          'This user has already been deleted',
          `Error ${user.blocked ? 'unblocking' : 'blocking'} user!`
        );
      } else {
        storeError.setErrorMessages(
          e.response.data.message,
          e.response.data.errors,
          e.response.status,
          'Error removing user!'
        );
      }
    });
}

const apiDomain = import.meta.env.VITE_API_DOMAIN
const userPhotoUrl = (user) => {
  const photoFile = user.photo_filename
  if (photoFile) {
    return `http://${apiDomain}/storage/photos/${photoFile}`
  }
  return avatarNoneAssetURL
}

function createAdmin() {
  router.push('/register')
}
</script>

<template>
  <div class="mb-5 mt-5">
    <label for="username" class="block text-sm font-medium text-gray-700">Search by Name:</label>
    <Input v-model="username" placeholder="Name..." />
  </div>

  <div class="users-list flex flex-row flex-wrap justify-center">
    <Card
      v-for="user in filteredUsers"
      :key="user.id"
      class="shadow-md h-[22rem] w-[16rem] relative flex flex-col m-2"
    >
      <CardHeader class="flex flex-col items-center justify-center flex-shrink-0 space-y-2">
        <div class="w-[7rem] h-[7rem] overflow-hidden rounded-full flex-shrink-0">
          <img
            :src="userPhotoUrl(user)"
            alt="User Photo"
            class="object-cover w-full h-full"
          />
        </div>
        <CardTitle class="text-base font-bold text-center w-[80%] line-clamp-2" :title="user.name">
          {{ user.name }}
        </CardTitle>
        <p class="text-xl font-bold text-muted-foreground text-center w-[80%]">
            <span :class="storeAuth.user.id === user.id ? 'text-green-500' : ''">
            {{ user.nickname }} 
            </span>
          <span v-if="user.type === 'A'" class="text-orange-500"> [Admin]</span>
        </p>
      </CardHeader>

      <CardContent class="flex-grow text-center">
        <CardDescription>{{ user.email }}</CardDescription>
      </CardContent>

      <CardFooter class="flex justify-between absolute bottom-0 left-0 right-0 p-3">
        <!-- Special button for the default admin card -->
        <Button 
          v-if="user.id === 'default-admin'"
          class="bg-blue-500 mx-auto" 
          @click="createAdmin"
        >
          Create Account
        </Button>
        
        <!-- Regular buttons for other users -->
        <template v-else>
          <Button 
            v-if="storeAuth.user.id !== user.id"
            :class="user.blocked ? 'bg-green-500' : 'bg-orange-500'" 
            @click="toggleBlock(user)"
          >
            {{ user.blocked ? 'Unblock' : 'Block' }}
          </Button>
          <Button class="bg-red-500" v-if="storeAuth.user.id !== user.id" @click="removeUser(user)">Remove</Button>
        </template>
      </CardFooter>
    </Card>
  </div>
</template>
