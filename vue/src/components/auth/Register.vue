<script setup>
import { ref, computed } from 'vue'
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

const title = computed(() => {
  return storeAuth.user?.type === 'A' ? 'Creating Administrator Account' : 'Register'
})

const isFormValid = computed(() => {
  return credentials.value.email && credentials.value.name && credentials.value.nickname && credentials.value.password
})

const register = async (credentials) => {
  console.log("credentials" + credentials.value)
  if (!isFormValid.value) {
    storeError.setError('Please fill all the fields.')
    return
  }
  const user = await storeAuth.register(credentials)
  if (user) {
    router.push({ name: 'login' })
  }
}

</script>

<template>
  <Card class="w-[450px] mx-auto my-8 p-4 px-8">
    <CardHeader>
      <CardTitle>{{ title }}</CardTitle>
      <CardDescription>Enter the fields to create your brand new account.</CardDescription>
    </CardHeader>
    <CardContent>
      <form>
        <div class="grid items-center w-full gap-4">
          <div class="flex flex-col space-y-1.5">
            <Label for="email">Email</Label>
            <Input id="email" type="email" placeholder="User Email" v-model="credentials.email" />
          </div>
          <div class="flex flex-col space-y-1.5">
            <Label for="name">Name</Label>
            <Input id="name" type="text" placeholder="Full Name" v-model="credentials.name" />
          </div>
          <div class="flex flex-col space-y-1.5">
            <Label for="nickname">Nickname</Label>
            <Input id="nickname" type="text" placeholder="Nickname" v-model="credentials.nickname" />
          </div>
          <div class="flex flex-col space-y-1.5">
            <Label for="password">Password</Label>
            <Input id="password" type="password" v-model="credentials.password" />
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
      <Button :disabled="!isFormValid" @click="register"> Register </Button>
    </CardFooter>
  </Card>
</template>