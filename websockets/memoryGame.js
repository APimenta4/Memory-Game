import { ref, reactive } from "vue";

export function useMemoryGame(board) {
  const cards = ref([]);
  const flippedCards = reactive({ indexes: [], values: [] });
  const isGameOver = ref(false);
  const totalTurns = ref(0);
  const startTime = ref(null);
  const endTime = ref(null);

  let flipTimeout = null;

  const cardPairs = (board.board_cols * board.board_rows) / 2;

  // Generate shuffled cards
  const generateCards = () => {
    const numbers = Array.from({ length: cardPairs }, (_, i) => i + 1);
    const deck = [...numbers, ...numbers]
      .sort(() => Math.random() - 0.5)// Shuffle
      .map((value) => ({
        value,
        isFlipped: false,
        isMatched: false,
      }));
    cards.value = deck;

    // Reset tracking variables
    isGameOver.value = false;
    flippedCards.indexes = [];
    flippedCards.values = [];
    totalTurns.value = 0;
    startTime.value = null;
    endTime.value = null;
    flipTimeout = null;
  };

  // Flip a card
  const flipCard = (index) => {
    const card = cards.value[index];

    if (card.isFlipped || card.isMatched) {
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
    flippedCards.indexes.push(index);
    flippedCards.values.push(card.value);

    // Check for match
    if (flippedCards.indexes.length === 2) {
      totalTurns.value++; // Increment turn count
      if (flippedCards.values[0] === flippedCards.values[1]) {
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
    flippedCards.indexes.forEach((index) => {
      cards.value[index].isMatched = true;
    });
    flippedCards.indexes = [];
    flippedCards.values = [];
    flipTimeout = null;
  };

  // Reset flipped cards if they are not matched
  const resetFlippedCards = () => {
    flippedCards.indexes.forEach((index) => {
      cards.value[index].isFlipped = false;
    });
    flippedCards.indexes = [];
    flippedCards.values = [];
    flipTimeout = null;
  };

  // Reset the game
  const resetGame = () => {
    generateCards();
  };

// Calculate total time taken
const getTotalTime = () => {
    if (!startTime.value || !endTime.value) return 0;
    let totalTimeInSeconds = (endTime.value - startTime.value) / 1000; // Ensure integer value
    return totalTimeInSeconds.toFixed(2); // Format to 2 decimal places
  };
  
  // Initialize the game
  generateCards();

  return {
    cards,
    isGameOver,
    flipCard,
    resetGame,
    totalTurns,
    getTotalTime,
  };
}
