<script setup>
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuGroup,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import router from "@/router";
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore()
let isHovered = false;
</script>
<template>
  <DropdownMenu>
    <DropdownMenuTrigger asChild>
      <span 
      :class="[
          'text-gray-900 px-3 py-2 rounded-md text-sm font-medium transition-colors cursor-pointer', 
          isHovered ? 'hover:text-blue-600' : ''
        ]"
        @mouseenter="isHovered = true"
        @mouseleave="isHovered = false"
      >
        Scoreboard
      </span>
    </DropdownMenuTrigger>
    <DropdownMenuContent>
      <DropdownMenuGroup>
        <DropdownMenuItem @click="router.push({ name: 'scoreboardGlobal' })">
          <span>Global</span>
        </DropdownMenuItem>
        <DropdownMenuItem v-if="authStore.user" @click="router.push({ name: 'scoreboardPersonal' })">
          <span>Personal</span>
        </DropdownMenuItem>
      </DropdownMenuGroup>
    </DropdownMenuContent>
  </DropdownMenu>
</template>