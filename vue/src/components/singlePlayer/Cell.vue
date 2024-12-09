<script setup>
const props = defineProps(['piece', 'index'])
const emit = defineEmits(['play'])

const play = () => {
    emit('play', props.index)
}
</script>

<template>
    <div class="grow">
      {{ index }}
        <!-- <img :src="''" alt="Tictactoe piece" class="w-full h-full"
            @click.prevent="play"> -->
    </div>
</template>