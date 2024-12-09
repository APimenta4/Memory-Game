import { ref, reactive } from "vue";

export function useMemoryGame(board) {
  const cards = ref([]);
  const flippedCards = reactive({ indexes: [], values: [] });
  const isGameOver = ref(false);

  // Calculate the number of pairs based on the board layout
  const cardPairs = (board.board_cols * board.board_rows) / 2;

  // Generate shuffled cards
  const generateCards = () => {
    const numbers = Array.from({ length: cardPairs }, (_, i) => i + 1); // Numbers 1 to `cardPairs`
    const deck = [...numbers, ...numbers]
      .sort(() => Math.random() - 0.5)
      .map((value) => ({
        value,
        isFlipped: false,
        isMatched: false,
      }));
    cards.value = deck;
    isGameOver.value = false;
    flippedCards.indexes = [];
    flippedCards.values = [];
  };

  // Flip a card
  const flipCard = (index) => {
    const card = cards.value[index];

    if (card.isFlipped || card.isMatched || flippedCards.indexes.length === 2) {
      return; // Ignore invalid clicks
    }

    card.isFlipped = true;
    flippedCards.indexes.push(index);
    flippedCards.values.push(card.value);

    // Check for match
    if (flippedCards.indexes.length === 2) {
      setTimeout(() => {
        checkMatch();
      }, 1000);
    }
  };

  // Check if flipped cards match
  const checkMatch = () => {
    const [firstIndex, secondIndex] = flippedCards.indexes;
    const [firstValue, secondValue] = flippedCards.values;

    if (firstValue === secondValue) {
      cards.value[firstIndex].isMatched = true;
      cards.value[secondIndex].isMatched = true;
    } else {
      cards.value[firstIndex].isFlipped = false;
      cards.value[secondIndex].isFlipped = false;
    }

    flippedCards.indexes = [];
    flippedCards.values = [];

    isGameOver.value = cards.value.every((card) => card.isMatched);
  };

  // Reset the game
  const resetGame = () => {
    generateCards();
  };

  // Initialize the game
  generateCards();

  return {
    cards,
    isGameOver,
    flipCard,
    resetGame,
  };
}
