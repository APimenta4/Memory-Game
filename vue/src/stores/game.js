import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import axios from 'axios'
import { useErrorStore } from '@/stores/error'

export const useGameStore = defineStore('game', () => {
    const storeError = useErrorStore()

    const game = ref({})
    const board = ref({})

    const reloadRequest = ref(0)

    

    const insertGame = async (newGame) => {
        storeError.resetMessages()
        try {
            const response = await axios.post('games', newGame)
            game.value = response.data.data

            console.log("game.js:insertGame game.value=" + game.value)
            console.log("game.js:insertGame response.data.data=" + response.data.data)
            return response.data.data
        } catch (e) {
            storeError.setErrorMessages(e.response.data.message, e.response.data.errors, e.response.status, 'Error creating game!')
            return false
        }
    }

    const updateGame = async (updateData) => {
        try {
            const response = await axios.put(`games/${game.value.id}`, updateData)
            game.value = response.data.data
            if (response.data.data.status === "PL" | response.data.data.status === "PE"){
                game.value = response.data.data
            } else {
                game.value = {}
            }
            
            return response.data.data
        } catch (e) {
            storeError.setErrorMessages(e.response.data.message, e.response.data.errors, e.response.status, 'Error updating game!')
            return false
        }
    }
    
    return {
        game, board, insertGame, updateGame, reloadRequest
    }
})
