<script setup>
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuGroup,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import router from "@/router";
import { useAuthStore } from "@/stores/auth";


const authStore =useAuthStore()
</script>
<template>
  <DropdownMenu>
    <DropdownMenuTrigger asChild>
      <img
        class="w-14 h-14 min-w-14 min-h-14 object-cover rounded-full cursor-pointer"
        :src="authStore.userPhotoUrl"
        alt="Profile Picture"
      />
    </DropdownMenuTrigger>
    <DropdownMenuContent>
      <DropdownMenuLabel>{{authStore.user.nickname}}</DropdownMenuLabel>
      <DropdownMenuSeparator />
      <DropdownMenuGroup>
        <DropdownMenuItem @click="router.push({ name: 'profile' })">
          <span>See Profile</span>
        </DropdownMenuItem>
        <DropdownMenuItem @click="router.push({ name: 'profileEdit' })">
          <span>Edit Profile</span>
        </DropdownMenuItem>
      </DropdownMenuGroup>
      <DropdownMenuSeparator />
      <DropdownMenuItem @click="authStore.logout()">
        <span>Log out</span>
      </DropdownMenuItem>
    </DropdownMenuContent>
  </DropdownMenu>
</template>