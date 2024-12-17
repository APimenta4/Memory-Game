<script setup>
import { onMounted, ref } from 'vue'
import { useAuthStore } from '@/stores/auth' // For userPhotoUrl
import { Button } from '@/components/ui/button'
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

const storeAuth = useAuthStore() // Fetch userPhotoUrl
const newPhotoFile = ref(null) // New photo file to upload

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

const uploadPhoto = async () => {
    if (!newPhotoFile.value) {
        alert("Please select a file to upload!");
        return;
    }

    try {
        const photoFilename = await storeAuth.updatePhoto(newPhotoFile.value);
        userData.value.photo = photoFilename; // Store the filename in userData for the update
        alert("Profile photo uploaded successfully!");
    } catch (error) {
        console.error("Error uploading photo:", error);
        alert("Failed to upload photo.");
    }
};

const updateProfile = async () => {
    const updatedData = {};

    // Include only fields that have values (non-empty)
    if (userData.value.name) updatedData.name = userData.value.name;
    if (userData.value.email) updatedData.email = userData.value.email;
    if (userData.value.nickname) updatedData.nickname = userData.value.nickname;
    if (userData.value.password) updatedData.password = userData.value.password;

    try {
        if (newPhotoFile.value) {
            // Upload photo and include filename in profile update
            const photoFilename = await storeAuth.updatePhoto(newPhotoFile.value);
            updatedData.photo_filename = photoFilename;
        }

        await storeAuth.updateUser(updatedData);
        alert("Profile updated successfully!");

        // Optionally refresh the user data in the store
        await storeAuth.fetchUser();
    } catch (error) {
        console.error("Error updating profile:", error);
        alert("Failed to update profile.");
    }
};

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
                    <!-- Display the current user photo -->
                    <div class="flex justify-center mb-4">
                        <img v-if="storeAuth.userPhotoUrl" :src="storeAuth.userPhotoUrl" alt="User Profile"
                            class="w-32 h-32 object-cover rounded-lg border" />
                    </div>

                    <!-- Upload new photo -->
                    <div class="flex items-center justify-start space-x-4">
                        <input type="file" accept="image/*" @change="handleFileChange"
                            class="text-sm text-gray-500 file:mr-2 file:py-1 file:px-3 file:border file:rounded-md file:text-sm file:font-medium file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100" />
                        <Button @click.prevent="uploadPhoto" variant="outline" class="px-4">
                            Upload Photo
                        </Button>
                    </div>
                </div>



                <!-- Email -->
                <div class="flex flex-col space-y-1.5">
                    <Label for="email">Email</Label>
                    <Input id="email" type="email" placeholder="User Email" v-model="userData.email" />
                </div>

                <!-- Nickname -->
                <div class="flex flex-col space-y-1.5">
                    <Label for="nickname">Nickname</Label>
                    <Input id="nickname" type="text" placeholder="Nickname" v-model="userData.nickname" />
                </div>

                <!-- Name -->
                <div class="flex flex-col space-y-1.5">
                    <Label for="name">Name</Label>
                    <Input id="name" type="text" placeholder="Full Name" v-model="userData.name" />
                </div>

                <!-- Password -->
                <div class="flex flex-col space-y-1.5">
                    <Label for="password">New Password</Label>
                    <Input id="password" type="password" placeholder="New Password" v-model="userData.password" />
                </div>
            </form>
        </CardContent>

        <CardFooter class="flex justify-between px-6 pb-6">
            <Button variant="outline" @click="$router.back()">
                Cancel
            </Button>
            <Button @click.prevent="updateProfile">
                Save Changes
            </Button>
        </CardFooter>
    </Card>
</template>
