<script setup>
import { Card, CardTitle } from '@/components/ui/card';

// Data to be fetched
const props = defineProps({
  board: {
    type: Object,
    required: true,
  },
})

</script>

<template>
<!-- <router-link :to="``" class="block"> -->
  <Card class="w-96 h-64 flex flex-col justify-center items-center p-6 hover:shadow-lg cursor-pointer">
    <CardTitle>
      {{ board.board_size }}
    </CardTitle>
    <div>Additional content</div>
  </Card>
<!-- </router-link> -->
</template>