<script setup>
import { ref, computed } from 'vue';
import Toaster from './components/ui/toast/Toaster.vue';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();

const showDropdown = ref(false);

const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value;
};

const isLoggedIn = computed(() => !!authStore.user);

const navigationBarClass = computed(() => {
  return isLoggedIn.value
    ? 'text-gray-900 hover:text-blue-600 cursor-pointer'
    : 'text-gray-400 cursor-default pointer-events-none';
});
</script>

<template>
  <Toaster />
  <div class="min-h-screen bg-gray-50">
    <header class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="flex items-center justify-start h-16 space-x-8">
          <RouterLink to="/"
            class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            active-class="text-blue-600 font-semibold">
            Home
          </RouterLink>
          <RouterLink to="/testers/laravel"
            class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            active-class="text-blue-600 font-semibold">
            Laravel Tester
          </RouterLink>
          <RouterLink to="/testers/websocket"
            class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            active-class="text-blue-600 font-semibold">
            WebSockets Tester
          </RouterLink>
          <RouterLink :to="isLoggedIn ? '/history' : null"
            :class="[navigationBarClass, 'px-3 py-2 rounded-md text-sm font-medium transition-colors']"
            active-class="text-blue-600 font-semibold" :disabled="!isLoggedIn">
            Game History
          </RouterLink>
          <RouterLink :to="isLoggedIn ? '/historyVertical' : null"
            :class="[navigationBarClass, 'px-3 py-2 rounded-md text-sm font-medium transition-colors']"
            active-class="text-blue-600 font-semibold" :disabled="!isLoggedIn">
            Game History Vertical
          </RouterLink>
          <div class="relative" @mouseenter="toggleDropdown" @mouseleave="toggleDropdown">
            <span
              class="text-gray-900 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors cursor-pointer">
              Scoreboard
            </span>
            <div v-if="showDropdown" class="absolute bg-white shadow-lg rounded-md mt-2 z-50">
              <RouterLink to="/scoreboard/global"
                class="block px-4 py-2 text-gray-900 hover:text-blue-600 rounded-t-md">
                Global
              </RouterLink>
              <RouterLink :to="isLoggedIn ? '/scoreboard/personal' : null"
                :class="[navigationBarClass, 'block px-4 py-2 rounded-t-md']" :disabled="!isLoggedIn">
                Personal
              </RouterLink>
            </div>
          </div>
        </nav>
      </div>
    </header>
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <RouterView />
    </main>
  </div>
</template>