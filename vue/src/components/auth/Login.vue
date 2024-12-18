<script setup>
import { ref } from 'vue'
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
import { useToast } from '@/components/ui/toast/use-toast'



const router = useRouter()

const { toast } = useToast()
const storeAuth = useAuthStore()
const storeError = useErrorStore()
const responseData = ref('')


const credentials = ref({
  email: '',
  password: ''
})

const cancel = () => {
  router.back()
}

const login = async () => {
  if (!credentials.value.email || !credentials.value.password) {
    toast({
      title: 'Failed to log in!',
      description:
        "You must provide your email and password to log in.",
      variant: 'destructive',
    }) 
    return
  }
  const user = await storeAuth.login({
    email: credentials.value.email,
    password: credentials.value.password
  })
  responseData.value = user.name
}
</script>

<template>
  <Card class="w-[450px] mx-auto my-8 p-4 px-8">
    <CardHeader>
      <CardTitle>Login</CardTitle>
      <CardDescription>Enter your credentials to access your account.</CardDescription>
    </CardHeader>
    <CardContent>
      <form>
        <div class="grid items-center w-full gap-4">
          <div class="flex flex-col space-y-1.5">
            <Label for="email">Email</Label>
            <Input id="email" type="email" placeholder="User Email" v-model="credentials.email" />
          </div>
          <div class="flex flex-col space-y-1.5">
            <Label for="password">Password</Label>
            <Input id="password" type="password" placeholder="Password" v-model="credentials.password" />
          </div>
        </div>
      </form>
    </CardContent>
    <CardFooter class="flex justify-between px-6 pb-6">
      <Button variant="outline" @click="cancel"> Cancel </Button>
      <Button :disabled="!credentials.email || !credentials.password" @click="login"> Login </Button>
    </CardFooter>
  </Card>
</template>
