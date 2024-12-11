import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import axios from 'axios'
import { useErrorStore } from '@/stores/error'

export const useGameStore = defineStore('game', () => {
    const storeError = useErrorStore()

    const game = ref({})
    const board = ref({})
    

    const insertGame = async (newGame) => {
        storeError.resetMessages()
        try {
            const response = await axios.post('games', newGame)
            game.value = response.data.data
            return response.data.data
        } catch (e) {
            storeError.setErrorMessages(e.response.data.message, e.response.data.errors, e.response.status, 'Error creating game!')
            return false
        }
    }

    const updateGame = async (updateData) => {
        try {
            const response = await axios.patch(`games/${game.value.id}`, updateData)
            game.value = response.data.data
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
