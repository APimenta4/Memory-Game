import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import axios from 'axios'
import { useErrorStore } from '@/stores/error'

export const useGameStore = defineStore('game', () => {
    const storeError = useErrorStore()

    const game = ref({})
    const board = ref({})
    

    const insertGame = async (game) => {
        storeError.resetMessages()
        try {
            const response = await axios.post('games', game)
            return response.data.data
        } catch (e) {
            storeError.setErrorMessages(e.response.data.message, e.response.data.errors, e.response.status, 'Error creating game!')
            return false
        }
    }

    const updateGame = async (game) => {
        storeError.resetMessages()
        try {
            const response = await axios.put('games/' + game.id, game)
            return response.data.data
        } catch (e) {
            storeError.setErrorMessages(e.response.data.message, e.response.data.errors, e.response.status, 'Error updating game!')
            return false
        }
    }
    
    return {
        game, board, insertGame, updateGame
    }
})
