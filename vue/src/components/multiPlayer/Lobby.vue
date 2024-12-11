<script setup>
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card'
import { Button } from '@/components/ui/button';
import { ScrollArea } from '@/components/ui/scroll-area';
import ListGamesLobby from './ListGamesLobby.vue'
import { useLobbyStore } from '@/stores/lobby'
import { useAuthStore } from '@/stores/auth';

const storeAuth = useAuthStore()
const storeLobby = useLobbyStore()
</script>

<template>
    <Card :class="{'opacity-50': !storeAuth.user, 'pointer-events-none': !storeAuth.user}" class="my-8 py-2 px-1 h-full max-h-screen overflow-hidden flex flex-col">
        <CardHeader class="pb-0">
            <CardTitle>Lobby</CardTitle>
            <CardDescription v-if="storeAuth.user">{{ storeLobby.totalGames == 1 ? '1 game' : storeLobby.totalGames + ' games'}} waiting.</CardDescription>
            <CardDescription v-else>You must be logged in to play multiplayer games!</CardDescription>
        </CardHeader>
        <CardContent v-if="storeAuth.user" class="p-4 flex-1 overflow-hidden flex flex-col">
            <div class="py-2">
                <Button @click="storeLobby.addGame(1)"> <!-- TODO fazer com que o user escolha o board -->
                    New Game
                </Button>
            </div>
            <ScrollArea class="flex-1 overflow-auto">
                <div v-if="storeLobby.totalGames > 0">
                    <ListGamesLobby></ListGamesLobby>
                </div>
                <div v-else>
                    <h2 class="text-xl">The lobby is empty!</h2>
                </div>
            </ScrollArea>
        </CardContent>
    </Card>
</template>