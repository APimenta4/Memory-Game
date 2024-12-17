import { ref } from "vue";

export function useMemoryGame(board) {
    const cards = ref([]);
    const flippedCards = ref({ indexes: [], values: [] });
    const isGameOver = ref(false);
    const totalTurns = ref(0);
    const pairsFound = ref(0);
    const startTime = ref(null);
    const endTime = ref(null);

    let flipTimeout = null;

    const cardPairs = (board.board_cols * board.board_rows) / 2;

    // Generate shuffled cards
    const generateCards = () => {
        const numbers = Array.from({ length: cardPairs }, (_, i) => i + 1);
        const deck = [...numbers, ...numbers]
        //.sort(() => Math.random() - 0.5)// Shuffle
        .map((value) => ({
            value,
            isFlipped: false,
            isMatched: false,
        }));
        cards.value = deck;

        // Reset tracking variables
        isGameOver.value = false;
        flippedCards.value.indexes = [];
        flippedCards.value.values = [];
        totalTurns.value = 0;
        startTime.value = null;
        endTime.value = null;
        flipTimeout = null;
    };

    // Flip a card
    const flipCard = (index) => {
        const card = cards.value[index];

        if ((card.isFlipped && flippedCards.value.indexes.length === 1 )|| card.isMatched) {
            return;
        }

        // Start the timer on the first card flip
        if (!startTime.value) {
            startTime.value = Date.now();
        }

        // Cancel ongoing reset timeout
        if (flipTimeout) {
            clearTimeout(flipTimeout);
            resetFlippedCards();
        }

        // Flip the selected card
        card.isFlipped = true;
        flippedCards.value.indexes.push(index);
        flippedCards.value.values.push(card.value);

        // Check for match
        if (flippedCards.value.indexes.length === 2) {
            totalTurns.value++; // Increment turn count
            if (flippedCards.value.values[0] === flippedCards.value.values[1]) {
                markAsMatched();

                // Check if the game is over
                if (cards.value.every((card) => card.isMatched)) {
                    isGameOver.value = true;
                    endTime.value = Date.now(); // Record the end time
                }
            } else {
                // Schedule a timeout to reset mismatched cards
                flipTimeout = setTimeout(() => {
                    resetFlippedCards();
                }, 1000);
            }
        }
    };

    // Mark flipped cards as matched
    const markAsMatched = () => {
        pairsFound.value++
        flippedCards.value.indexes.forEach((index) => {
        cards.value[index].isMatched = true;
        });
        flippedCards.value.indexes = [];
        flippedCards.value.values = [];
        flipTimeout = null;
    };

    // Reset flipped cards if they are not matched
    const resetFlippedCards = () => {
        flippedCards.value.indexes.forEach((index) => {
        cards.value[index].isFlipped = false;
        });
        flippedCards.value.indexes = [];
        flippedCards.value.values = [];
        flipTimeout = null;
    };

    // Reset the game
    const resetGame = () => {
        cards.value = [];
        flippedCards.value = { indexes: [], values: [] };
        isGameOver.value = false;
        totalTurns.value = 0;
        pairsFound.value = 0;
        startTime.value = null;
        endTime.value = null;
        flipTimeout = null;
        generateCards();
    };

    // Calculate total time taken
    const getCurrentTime = () => {
        if (!startTime.value) return "0.0";
        if (isGameOver.value) return getTotalTime();
        let totalTimeInSeconds = (Date.now() - startTime.value) / 1000;
        return totalTimeInSeconds.toFixed(1);
    };

    // Calculate total time taken
    const getTotalTime = () => {
        if (!startTime.value || !endTime.value) return "0.00";
        let totalTimeInSeconds = (endTime.value - startTime.value) / 1000; // Ensure integer value
        return totalTimeInSeconds.toFixed(2); // Format to 2 decimal places
    };


    // Initialize the game
    generateCards();

    return {
        startTime,
        endTime,
        cards,
        isGameOver,
        flipCard,
        resetGame,
        totalTurns,
        pairsFound,
        getCurrentTime,
        getTotalTime
    };
}
