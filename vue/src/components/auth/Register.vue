<script setup>
import { inject, ref } from 'vue'
import { useRouter } from 'vue-router'
import { Button } from '@/components/ui/button'
import { useErrorStore } from '@/stores/error'
import { useAuthStore } from '@/stores/auth'
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle
} from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

const router = useRouter()

const storeAuth = useAuthStore()
const storeError = useErrorStore()

const credentials = ref({
  email: '',
  name: '',
  nickname: '',
  password: '',
  photo: null
})




const cancel = () => {
  router.back()
}
</script>

<template>
  <Card class="w-[450px] mx-auto my-8 p-4 px-8">
    <CardHeader>
      <CardTitle>Register</CardTitle>
      <CardDescription>Enter the fields to create your brand new account.</CardDescription>
    </CardHeader>
    <CardContent>
      <form>
        <div class="grid items-center w-full gap-4">
          <div class="flex flex-col space-y-1.5">
            <Label for="email">Email</Label>
            <Input id="email" type="email" placeholder="User Email" v-model="credentials.email" />
            <!-- <ErrorMessage :errorMessage="storeError.fieldMessage('email')"></ErrorMessage> -->
          </div>
          <div class="flex flex-col space-y-1.5">
            <Label for="name">Name</Label>
            <Input id="name" type="text" placeholder="Full Name" v-model="credentials.name" />
            <!-- <ErrorMessage :errorMessage="storeError.fieldMessage('name')"></ErrorMessage> -->
          </div>
          <div class="flex flex-col space-y-1.5">
            <Label for="nickname">Nickname</Label>
            <Input id="nickname" type="text" placeholder="Nickname" v-model="credentials.nickname" />
            <!-- <ErrorMessage :errorMessage="storeError.fieldMessage('nickname')"></ErrorMessage> -->
          </div>
          <div class="flex flex-col space-y-1.5">
            <Label for="password">Password</Label>
            <Input id="password" type="password" v-model="credentials.password" />
            <!-- <ErrorMessage :errorMessage="storeError.fieldMessage('password')"></ErrorMessage> -->
          </div>
          <div class="flex flex-col space-y-1.5">
            <Label for="photo">Avatar (optional)</Label>
            <Input id="photo" type="file" @change="e => credentials.photo = e.target.files[0]" />
          </div>
        </div>
      </form>
    </CardContent>
    <CardFooter class="flex justify-between px-6 pb-6">
      <Button variant="outline" @click="cancel"> Cancel </Button>
      <Button @click="storeAuth.register(credentials)"> Register </Button>
    </CardFooter>
  </Card>
</template>