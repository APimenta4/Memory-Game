<script setup>
import { Table,TableBody,TableCell,TableHead,TableHeader,TableRow } from '@/components/ui/table';
import { Select,SelectContent,SelectGroup,SelectItem,SelectLabel,SelectTrigger,SelectValue } from '@/components/ui/select';
import { Card,CardContent,CardDescription,CardFooter,CardHeader,CardTitle } from '@/components/ui/card';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { ref, onMounted, watch } from 'vue';
import { useBoardStore } from '@/stores/board';
import { useErrorStore } from '@/stores/error';
import axios from 'axios';

const storeBoard = useBoardStore();
const storeError = useErrorStore();

const games = ref([]);
const multiplayerStatistics = ref([]);

const scoreboardBoardId = ref('');
const scoreboardType = ref('time');

// Fetch the singleplayer scoreboards
const fetchScoreboardGames = async () => {
    storeError.resetMessages()
    try {
        const response = await axios.get(`/scoreboards/global/singleplayer`, {
            params: {
                board_id: scoreboardBoardId.value,
                scoreboard_type: scoreboardType.value,
            },
        });
        games.value = response.data.data;
        return true;
    } catch (e) {
        storeError.setErrorMessages(e.response.data.message, e.response.data.errors, e.response.status, 'Error fetching games!')
        return false;
    }
};

// Fetch the multiplayer statistics
const fetchMultiplayerStatistics = async () => {
    storeError.resetMessages()
    try {
        const response = await axios.get(`/scoreboards/global/multiplayer/`);
        multiplayerStatistics.value = response.data;
        return true;
    } catch (e) {
        storeError.setErrorMessages(e.response.data.message, e.response.data.errors, e.response.status, 'Error fetching statistics!')
        return false;
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
    fetchMultiplayerStatistics();
});

// Watch for changes in scoreboardBoardId and scoreboardType to refetch games
watch([scoreboardBoardId, scoreboardType], () => {
    fetchScoreboardGames();
});
</script>

<template>
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
                        <li v-for="player in multiplayerStatistics" :key="player.nickname">
                            {{ player.position }}. {{ player.nickname }} - {{ player.victories }} victories 👑
                        </li>
                    </ul>
                </CardContent>
                <CardFooter></CardFooter>
            </Card>
        </div>

        <div class="flex-2 flex-grow">
            <div class="flex gap-4 mb-4">
                <h4 class="text-2xl font-semibold leading-none tracking-tight">Singleplayer Games</h4>
            </div>
            <div class="flex gap-4 mb-4">
                <div class="flex-grow max-w-xs">
                    <!-- Select -->
                    <Select v-model="scoreboardBoardId">
                        <SelectTrigger>
                            <SelectValue :placeholder="'Select a board size'" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectLabel>Select Board Size</SelectLabel>
                                <SelectItem v-for="board in storeBoard.boards" :key="board.id"
                                    :value="String(board.id)">
                                    {{ board.board_size }}
                                </SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                </div>

                <!-- RadioGroup -->
                <div>
                    <RadioGroup v-model="scoreboardType" class="flex">
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
                    <TableRow v-for="game in games" :key="game.id">
                        <TableCell>{{ game.creator_nickname }}</TableCell>
                        <TableCell>{{ game.id }}</TableCell>
                        <TableCell>{{ game.board_size }}</TableCell>
                        <TableCell>{{ formatDate(game.began_at) }}</TableCell>
                        <TableCell>{{ game.total_time }}</TableCell>
                        <TableCell>{{ game.total_turns_winner }}</TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </div>
</template>