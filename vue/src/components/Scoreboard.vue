<script setup>
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
const userName = authStore.user?.nickname;

const personalGames = ref([]);
const globalGames = ref([]);
const boards = ref([]);

const userMultiplayerStats = ref([]);
const globalMultiplayerStats = ref([]);

// Selected options
const scoreboardBoardIdPersonal = ref('');
const scoreboardTypePersonal = ref('time');

const scoreboardBoardIdGlobal = ref('');
const scoreboardTypeGlobal = ref('time');

// Fetch the singleplayer scoreboards
const fetchScoreboardGames = async (isPersonal) => {
    try {
        if (isPersonal) {
            const response = await axios.get(`/scoreboard/personal/singleplayer`, {
                params: {
                    board_id: scoreboardBoardIdPersonal.value,
                    scoreboard_type: scoreboardTypePersonal.value,
                },
            });
            personalGames.value = response.data.data;
        } else {
            const response = await axios.get(`/scoreboard/global/singleplayer`, {
                params: {
                    board_id: scoreboardBoardIdGlobal.value,
                    scoreboard_type: scoreboardTypeGlobal.value,
                },
            });
            globalGames.value = response.data.data;
        }
    } catch (error) {
        console.error('Error fetching scoreboard games history:', error);
    }
};

// Fetch the multiplayer statistics
const fetchMultiplayerStatistics = async () => {
    try {
        const response = await axios.get(`/scoreboard/personal/multiplayer/`);
        userMultiplayerStats.value = response.data;
    } catch (error) {
        console.error('Error fetching user multiplayer games history:', error);
    }
    try {
        const response = await axios.get(`/scoreboard/global/multiplayer/`);
        globalMultiplayerStats.value = response.data;
    } catch (error) {
        console.error('Error fetching global multiplayer games history:', error);
    }
};

// Fetch available boards
const fetchBoards = async () => {
    try {
        const response = await axios.get('/boards');
        boards.value = response.data.data;

        // Set the first incoming board as the default selected board
        if (boards.value.length > 0) {
            scoreboardBoardIdPersonal.value = boards.value[0].id;
            scoreboardBoardIdGlobal.value = boards.value[0].id;
        }
    } catch (error) {
        console.error('Error fetching boards:', error);
    }
};

// Change data format (passing undefined to LocaleString lets javascript decide the format)
const formatDate = (dateString) => {
    const options = {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    };
    return new Date(dateString).toLocaleString(undefined, options);
};

onMounted(() => {
    fetchBoards();
    fetchMultiplayerStatistics();
});


// The first fetch of the personal games is triggered through these watches when fetchBoards() sets the default board
// Watch for changes in scoreboardBoardIdPersonal and scoreboardTypePersonal to refetch personal games
watch([scoreboardBoardIdPersonal, scoreboardTypePersonal], () => {
    fetchScoreboardGames(true);
});

// Watch for changes in scoreboardBoardIdGlobal and scoreboardTypeGlobal to refetch global games
watch([scoreboardBoardIdGlobal, scoreboardTypeGlobal], () => {
    fetchScoreboardGames(false);
});

</script>

<template>
    <h1 class="text-3xl font-bold mb-4">Personal Scoreboard</h1>
    <div class="flex gap-4 items-start">
        <!-- Card Section -->
        <div class="flex-1 max-w-xs">
            <Card>
                <CardHeader>
                    <CardTitle>Multiplayer Games</CardTitle>
                    <p class="text-2xl font-bold text-muted-foreground">{{ userName }}</p>
                    <CardDescription>Your total multiplayer victories and losses</CardDescription>

                </CardHeader>
                <CardContent>
                    <div v-if="userMultiplayerStats.victories === 0 && userMultiplayerStats.losses === 0">
                        <p>You haven't played any multiplayers games yet!</p>
                    </div>
                    <div v-else>
                        <p>Victories: {{ userMultiplayerStats.victories }} 👑</p>
                        <p>Losses: {{ userMultiplayerStats.losses }} ❌</p>
                        <p>Win percentage: {{ userMultiplayerStats.win_percentage }}%</p>
                    </div>
                </CardContent>
                <CardFooter>
                </CardFooter>
            </Card>
        </div>

        <div class="flex-2 flex-grow">
            <div class="flex gap-4 mb-4">
                <h4 class="text-2xl font-semibold leading-none tracking-tight">Singleplayer Games</h4>
            </div>
            <div class="flex gap-4 mb-4">
                <div class="flex-grow max-w-xs">
                    <!-- Select -->
                    <Select v-model="scoreboardBoardIdPersonal">
                        <SelectTrigger>
                            <SelectValue :placeholder="'Select a board size'" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectLabel>Select Board Size</SelectLabel>
                                <SelectItem v-for="board in boards" :key="board.id" :value="board.id">
                                    {{ board.board_size }}
                                </SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                </div>

                <!-- RadioGroup -->
                <div>
                    <RadioGroup v-model="scoreboardTypePersonal" class="flex">
                        <div class="flex items-center space-x-2">
                            <RadioGroupItem id="time" value="time" />
                            <label for="time">Best Time</label>
                        </div>
                        <div class="flex items-center space-x-2">
                            <RadioGroupItem id="turns" value="turns" />
                            <label for="turns">Minimum Turns</label>
                        </div>
                    </RadioGroup>
                </div>
            </div>

            <!-- Table -->
            <Table v-if="(personalGames.length>0)">
                <TableHeader>
                    <TableRow>
                        <TableHead>Game ID</TableHead>
                        <TableHead>Board Size</TableHead>
                        <TableHead>Start Time</TableHead>
                        <TableHead>Total Time</TableHead>
                        <TableHead>Total Turns</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="game in personalGames" :key="game.id">
                        <TableCell>{{ game.id }}</TableCell>
                        <TableCell>{{ game.board_size }}</TableCell>
                        <TableCell>{{ formatDate(game.began_at) }}</TableCell>
                        <TableCell>{{ game.total_time }}</TableCell>
                        <TableCell>{{ game.total_turns_winner }}</TableCell>
                    </TableRow>
                </TableBody>
            </Table>
            <div v-else class="ml-1">
                <p>You haven't played any singleplayer games yet!</p>
            </div>
        </div>
    </div>
    <br>
    <h1 class="text-3xl font-bold mb-4">Global Scoreboard</h1>
    <div class="flex gap-4 items-start">
        <!-- Card Section -->
        <div class="flex-1 max-w-xs">
            <Card>
                <CardHeader>
                    <CardTitle>Multiplayer Games</CardTitle>
                    <CardDescription>Top 5 players with the most multiplayer victories</CardDescription>
                </CardHeader>
                <CardContent>
                    <ul>
                        <li v-for="player in globalMultiplayerStats" :key="player.nickname">
                            {{ player.position }}. {{ player.nickname }} - {{ player.victories }} victories 👑
                        </li>
                    </ul>
                </CardContent>
                <CardFooter>

                </CardFooter>
            </Card>
        </div>

        <div class="flex-2 flex-grow">
            <div class="flex gap-4 mb-4">
                <h4 class="text-2xl font-semibold leading-none tracking-tight">Singleplayer Games</h4>
            </div>
            <div class="flex gap-4 mb-4">
                <div class="flex-grow max-w-xs">
                    <!-- Select -->
                    <Select v-model="scoreboardBoardIdGlobal">
                        <SelectTrigger>
                            <SelectValue :placeholder="'Select a board size'" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectLabel>Select Board Size</SelectLabel>
                                <SelectItem v-for="board in boards" :key="board.id" :value="board.id">
                                    {{ board.board_size }}
                                </SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                </div>

                <!-- RadioGroup -->
                <div>
                    <RadioGroup v-model="scoreboardTypeGlobal" class="flex">
                        <div class="flex items-center space-x-2">
                            <RadioGroupItem id="time" value="time" />
                            <label for="time">Best Time</label>
                        </div>
                        <div class="flex items-center space-x-2">
                            <RadioGroupItem id="turns" value="turns" />
                            <label for="turns">Minimum Turns</label>
                        </div>
                    </RadioGroup>
                </div>
            </div>

            <!-- Table -->
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Player</TableHead>
                        <TableHead>Game ID</TableHead>
                        <TableHead>Board Size</TableHead>
                        <TableHead>Start Time</TableHead>
                        <TableHead>Total Time</TableHead>
                        <TableHead>Total Turns</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="globalGame in globalGames" :key="globalGame.id">
                        <TableCell>{{ globalGame.creator.nickname }}</TableCell>
                        <TableCell>{{ globalGame.id }}</TableCell>
                        <TableCell>{{ globalGame.board_size }}</TableCell>
                        <TableCell>{{ formatDate(globalGame.began_at) }}</TableCell>
                        <TableCell>{{ globalGame.total_time }}</TableCell>
                        <TableCell>{{ globalGame.total_turns_winner }}</TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </div>
</template>