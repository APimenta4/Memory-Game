<script setup>
import Lobby from './Lobby.vue';
import Chat from './Chat.vue';
</script>

<template>
    <div class="flex gap-4 h-[30rem]">
        <div class="w-3/12">
            <Lobby />
        </div>
        <div class="w-9/12">
            <Chat />
        </div>
    </div>
</template>