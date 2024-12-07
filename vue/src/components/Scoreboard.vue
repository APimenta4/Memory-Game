<script setup>
import {
    Table,
    TableBody,
    TableCaption,
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

const personalGames = ref([]);
const boards = ref([]);

// Selected options
const scoreboardBoardId = ref('');
const scoreboardType = ref('time');

// Fetch the personal scoreboard
const fetchPersonalScoreboardGames = async () => {
    try {
        const response = await axios.get(`/scoreboard/myScoreboard`, {
            params: {
                board_id: scoreboardBoardId.value,
                scoreboard_type: scoreboardType.value,
                is_own_scoreboard: 1,
            },
        });
        personalGames.value = response.data.data;
    } catch (error) {
        console.error('Error fetching scoreboard games history:', error);
    }
};

// Fetch available boards
const fetchBoards = async () => {
    try {
        const response = await axios.get('/boards');
        boards.value = response.data.data;

        // Set the first incoming board as the default selected board
        if (boards.value.length > 0) {
            scoreboardBoardId.value = boards.value[0].id;
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
});

watch([scoreboardBoardId, scoreboardType], () => {
    fetchPersonalScoreboardGames();
});
</script>

<template>
    <h1 class="text-3xl font-bold mb-4">Personal Scoreboard</h1>

    <div class="flex gap-4 items-start">
        <!-- Card Section -->
        <div class="flex-1 max-w-xs">
            <Card>
                <CardHeader>
                    <CardTitle>Game History</CardTitle>
                    <CardDescription>Check your scores and game history</CardDescription>
                </CardHeader>
                <CardContent>
                    Game stats are fetched and displayed dynamically.
                </CardContent>
                <CardFooter>
                    Card Footer
                </CardFooter>
            </Card>
        </div>

        <div class="flex-2 flex-grow">
            <div class="flex gap-4 mb-4">
                <div class="flex-grow max-w-xs">
                    <!-- Select -->
                    <Select v-model="scoreboardBoardId" @change="fetchPersonalScoreboardGames()">
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
                    <RadioGroup v-model="scoreboardType" class="flex" @change="fetchPersonalScoreboardGames()">
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
        </div>
    </div>
    <br>
    <h1 class="text-3xl font-bold mb-4">Global Scoreboard</h1>

</template>