<script setup>
import { computed, onMounted, ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { Button } from '@/components/ui/button'
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle
} from '@/components/ui/card'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger
} from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { useErrorStore } from '@/stores/error'
import { useRouter } from 'vue-router'
import { useToast } from '@/components/ui/toast/use-toast'

import axios from 'axios'

const storeAuth = useAuthStore()
const newPhotoFile = ref(null)
const showRemoveDialog = ref(false) 
const confirmationText = ref('')
const storeError = useErrorStore()
const router = useRouter()
const { toast } = useToast()


onMounted(async () => {
  await storeAuth.fetchUser()
})

const userData = ref({
  email: storeAuth.userEmail,
  nickname: storeAuth.userNickname,
  name: storeAuth.userName,
  password: ''
})

// Handle file selection
const handleFileChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    newPhotoFile.value = file
  }
}

const updateProfile = async () => {
  const updatedData = {}

  if (userData.value.name) updatedData.name = userData.value.name
  if (userData.value.email) updatedData.email = userData.value.email
  if (userData.value.nickname) updatedData.nickname = userData.value.nickname
  if (userData.value.password) updatedData.password = userData.value.password

  try {
    if (newPhotoFile.value) {
      const photoFilename = await storeAuth.updatePhoto(newPhotoFile.value)
      updatedData.photo_filename = photoFilename
    }
    await storeAuth.updateUser(updatedData)
    toast({
      title: 'Profile updated!',
      description:
        'Your profile has been successfully updated.',
      variant: 'success',
    })
    await storeAuth.fetchUser()
  } catch (error) {
    console.error('Error updating profile:', error)
    storeError.setErrorMessages(
      "Failed to update profile",
      error.response.data.errors,
      error.response.status,
      'Error updating profile!'
    )
  }
}

const photo = computed(()=>{
  return newPhotoFile.value ? URL.createObjectURL(newPhotoFile.value): storeAuth.userPhotoUrl
})

const removeAccount = async () => {
  try {
    await axios.delete(`/users/${storeAuth.user.id}`)
    storeAuth.clearUser()
    router.push('/')
    return true
  } catch (e) {
    storeError.setErrorMessages(
      e.response.data.message,
      e.response.data.errors,
      e.response.status,
      'Error removing account!'
    )
    return false
  }
}
</script>

<template>
  <Card class="w-[500px] mx-auto my-8 p-4 px-8">
    <CardHeader>
      <CardTitle>User Profile</CardTitle>
      <CardDescription>Update your account details.</CardDescription>
    </CardHeader>

    <CardContent>
      <form class="grid items-center w-full gap-4">
        <!-- Profile Photo -->
        <div class="flex flex-col space-y-1.5">
          <Label for="photo">Profile Photo</Label>
          <div class="flex justify-center mb-4">
            <img
              v-if="storeAuth.userPhotoUrl"
              :src="photo"
              alt="User Profile"
              class="w-32 h-32 object-cover rounded-lg border"
            />
          </div>

          <div class="flex items-center justify-start space-x-4">
            <input
              type="file"
              accept="image/*"
              @change="handleFileChange"
              class="text-sm text-gray-500 file:mr-2 file:py-1 file:px-3 file:border file:rounded-md file:text-sm file:font-medium file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100"
            />
          </div>
        </div>

        <!-- Email -->
        <div class="flex flex-col space-y-1.5">
          <Label for="email">Email</Label>
          <Input id="email" type="email" placeholder="User Email" v-model="userData.email" />
          <ErrorMessage :errorMessage="storeError.fieldMessage('email')"></ErrorMessage>
        </div>

        <!-- Nickname -->
        <div class="flex flex-col space-y-1.5">
          <Label for="nickname">Nickname</Label>
          <Input id="nickname" type="text" placeholder="Nickname" v-model="userData.nickname" />
          <ErrorMessage :errorMessage="storeError.fieldMessage('nickname')"></ErrorMessage>
        </div>

        <!-- Name -->
        <div class="flex flex-col space-y-1.5">
          <Label for="name">Name</Label>
          <Input id="name" type="text" placeholder="Full Name" v-model="userData.name" />
          <ErrorMessage :errorMessage="storeError.fieldMessage('name')"></ErrorMessage>
        </div>

        <!-- Password -->
        <div class="flex flex-col space-y-1.5">
          <Label for="password">New Password</Label>
          <Input
            id="password"
            type="password"
            placeholder="New Password"
            v-model="userData.password"
          />
          <ErrorMessage :errorMessage="storeError.fieldMessage('password')"></ErrorMessage>
        </div>
      </form>
    </CardContent>

    <!-- Footer Buttons -->
    <CardFooter class="flex justify-between px-6 pb-6">
      <Dialog>
        <!-- Trigger Button -->

        <!-- Cancel and Save Buttons -->
        <Button variant="outline" @click="$router.back()"> Cancel </Button>
        <Button @click.prevent="updateProfile"> Save Changes </Button>
        <DialogTrigger as-child>
          <Button variant="danger" class="bg-red-600 text-white" v-if="storeAuth.user.type !=='A'" @click="showRemoveDialog = true">
            Remove Account
          </Button>
        </DialogTrigger>

        <!-- Dialog Content -->
        <DialogContent v-if="showRemoveDialog">
          <DialogHeader>
            <DialogTitle>Confirm Account Removal</DialogTitle>
            <DialogDescription>
              Are you sure you want to permanently remove your account? This action is irreversible, and you will lose all your progress and brain coins.
            </DialogDescription>
          </DialogHeader>
          <DialogFooter class="flex flex-col gap-2">
            <Input
              v-model="confirmationText"
              type="text"
              placeholder="Type 'I UNDERSTAND' to proceed"
              class="border p-2 font-bold"
            />
            <div class="flex justify-end gap-2">
              <Button variant="outline" @click="showRemoveDialog = false"> Cancel </Button>
              <Button
                variant="destructive"
                class="bg-red-600 text-white"
                :disabled="confirmationText !== 'I UNDERSTAND'"
                @click.prevent="removeAccount"
              >
                Confirm
              </Button>
            </div>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </CardFooter>
  </Card>
</template>
