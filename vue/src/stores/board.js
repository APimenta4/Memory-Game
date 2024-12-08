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


    // const totalBoards = computed(() => {
    //     return boards.value ? boards.value.length : 0
    // })

    // const listBoardsIncludingNull = computed(() => [null].concat(boards.value))

    // const listBoardsToFilter = computed(() => [
    //             null,
    //             {
    //                 'id': -1,
    //                 'filterDescription': '-- No board --'
    //             }
    //         ].concat(boards.value.map((p) => {
    //             return {
    //                 'id': p.id,
    //                 'filterDescription': p.name
    //             }
    //         })))
    
    // // This function is "private" - not exported by the store
    // const getIndexOfBoard = (boardId) => {
    //     return boards.value.findIndex((p) => p.id === boardId)
    // }
    
    // const fetchBoard = async (boardId) => {
    //     storeError.resetMessages()
    //     const response = await axios.get('boards/' + boardId)
    //     const index = getIndexOfBoard(boardId)
    //     if (index > -1) {
    //         // Instead of a direct assignment, object is cloned/copied to the array
    //         // This ensures that the object in the array is not the same as the object fetched
    //         boards.value[index] = Object.assign({}, response.data.data)  
    //     }
    //     return response.data.data
    // }

    // const insertBoard = async (board) => {
    //     storeError.resetMessages()
    //     try {
    //         const response = await axios.post('boards', board)
    //         boards.value.push(response.data.data)
    //         toast({
    //             description: `Board #${response.data.data.id} 
    //                           "${response.data.data.name}" was created!`,
    //             action: h(ToastAction, {
    //                 altText: `Open new board`,
    //                 onclick: () => {
    //                     router.push({ name: 'updateBoard', 
    //                                   params: {id: response.data.data.id} })
    //                 }
    //             }, {
    //                 default: () => `Open new board`,
    //             })
    //         })
    //         return response.data.data
    //     } catch (e) {
    //         storeError.setErrorMessages(e.response.data.message, e.response.data.errors, e.response.status, 'Error creating board!')
    //         return false
    //     }
    // }

    // const updateBoard = async (board) => {
    //     storeError.resetMessages()
    //     try {
    //         const response = await axios.put('boards/' + board.id, board)
    //         const index = getIndexOfBoard(board.id)
    //         if (index > -1) {
    //             // Instead of a direct assignment, object is cloned/copied to the array
    //             // This ensures that the object in the array is not the same as the object fetched
    //             boards.value[index] = Object.assign({}, response.data.data)  
    //         }
    //         toast({
    //             description: 'Board has been updated correctly!',
    //         })
    //         return response.data.data
    //     } catch (e) {
    //         storeError.setErrorMessages(e.response.data.message, e.response.data.errors, e.response.status, 'Error updating board!')
    //         return false
    //     }
    // }

    // const deleteBoard = async (board) => {
    //     storeError.resetMessages()
    //     try {
    //         await axios.delete('boards/' + board.id)
    //         const index = getIndexOfBoard(board.id)
    //         if (index > -1) {
    //             boards.value.splice(index, 1)
    //         }
    //         return true
    //     } catch (e) {
    //         storeError.setErrorMessages(e.response.data.message, e.response.data.errors, e.response.status, 'Error deleting board!')
    //         return false
    //     }
    // }    

    // return {
    //     boards, totalBoards, listBoardsIncludingNull, listBoardsToFilter,
    //     fetchBoards, fetchBoard, insertBoard, updateBoard, deleteBoard
    // }    
    return {
        boards, totalBoards,fetchBoards
    }
})