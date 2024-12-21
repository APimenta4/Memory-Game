import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from 'axios'
import { useErrorStore } from '@/stores/error'
import { toast } from '@/components/ui/toast'

export const useGameStore = defineStore('game', () => {
    const storeError = useErrorStore()

    const game = ref({})
    const board = ref({})

    const reloadRequestTop5 = ref(false)
    const reloadRequestMemoryGame = ref(false)

    const insertGame = async (newGame) => {
        storeError.resetMessages()
        try {        
            const response = await axios.post('games', newGame)
            game.value = response.data.data
            if (response.status == 422){
                toast({
                    title: 'Could not create a new Game!',
                    description:response.data.message,
                    variant:'destructive'
                })
                return false
            }
            return game.value
        } catch (e) {
            storeError.setErrorMessages(e.response.data.message, e.response.data.errors, e.response.status, 'Error creating game!')
            return false
        }
    }

    const updateGame = async (updateData) => {
        try {
            const response = await axios.patch(`games/${game.value.id}`, updateData)
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

    const updateGameWithId = async (id, updateData) => {
        try {
            const response = await axios.patch(`games/${id}`, updateData)
            game.value = response.data.data
            if (response.data.data.status === "PL" | response.data.data.status === "PE"){
                game.value = response.data.data
            } else {
                game.value = {}
            }
            // here we can't return true, or we will have issues in the caller function
            return
        } catch (e) {
            // treated in the caller function
            // storeError.setErrorMessages(e.response.data.message, e.response.data.errors, e.response.status, 'Error updating game!')
            return e.response
        }
    }
    
    return {
        game, board, insertGame, updateGame, updateGameWithId, reloadRequestTop5, reloadRequestMemoryGame
    }
})
