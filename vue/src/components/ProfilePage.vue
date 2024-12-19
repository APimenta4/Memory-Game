<script setup>
import { onMounted, ref, computed } from 'vue'
import { useAuthStore } from '@/stores/auth' 
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import Label from './ui/label/Label.vue';

const storeAuth = useAuthStore() 

// Declare a loading state for user data
const isLoading = ref(true)

onMounted(async () => {
    try {
        await storeAuth.fetchUser() 
        isLoading.value = false
    } catch (error) {
        isLoading.value = false
    }
})

// Map the computed properties from the store
const userEmail = computed(() => storeAuth.userEmail) // Reactive
const userNickname = computed(() => storeAuth.userNickname) // Reactive
const userName = computed(() => storeAuth.userName) // Reactive
const userPhotoUrl = computed(() => storeAuth.userPhotoUrl) // Reactive
</script>

<template>
    <Card class="w-[500px] mx-auto my-8 p-4 px-8">
        <CardHeader>
            <CardTitle>User Profile</CardTitle>
            <CardDescription>View your account details.</CardDescription>
        </CardHeader>

        <CardContent>
            <div class="grid items-center w-full gap-4">
                <!-- Profile Photo -->
                <div class="flex flex-col space-y-1.5">
                    <Label for="photo">Profile Photo</Label>
                    <div class="flex justify-center mb-4">
                        <img v-if="userPhotoUrl" :src="userPhotoUrl" alt="User Profile"
                            class="w-32 h-32 object-cover rounded-lg border" />
                    </div>
                </div>

                <!-- Email -->
                <div class="flex flex-col space-y-1.5">
                    <Label for="email">Email</Label>
                    <div class="text-gray-700">
                        {{ userEmail }}
                    </div>
                </div>

                <!-- Nickname -->
                <div class="flex flex-col space-y-1.5">
                    <Label for="nickname">Nickname</Label>
                    <div class="text-gray-700">
                        {{ userNickname }}
                    </div>
                </div>

                <!-- Name -->
                <div class="flex flex-col space-y-1.5">
                    <Label for="name">Name</Label>
                    <div class="text-gray-700">
                        {{ userName }}
                    </div>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
