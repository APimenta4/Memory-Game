import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import axios from 'axios'
import { useErrorStore } from '@/stores/error'

export const useBoardStore = defineStore('board', () => {
    const storeError = useErrorStore()

    const boards = ref([])
    
    const totalBoards = computed(() => {
        return boards.value ? boards.value.length : 0
    })

    const fetchBoards = async () => {
        storeError.resetMessages()
        try {
            const response = await axios.get('boards')
            boards.value = response.data.data
            return true
        }
        catch (e) {
            storeError.setErrorMessages(e.response.data.message, e.response.data.errors, e.response.status, 'Error fetching boards!')
            return false
        }
    }

    const defaultBoard = computed(() => {
        return boards.value && boards.value[0] ? boards.value[0].id : null
    })
    
    return {
        boards, totalBoards, defaultBoard, fetchBoards,
    }
})
