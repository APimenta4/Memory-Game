<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue'
import axios from 'axios'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow
} from '@/components/ui/table'
import {
  Pagination,
  PaginationList,
  PaginationEllipsis,
  PaginationListItem,
  PaginationFirst,
  PaginationLast,
  PaginationNext,
  PaginationPrev
} from '@/components/ui/pagination'
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectLabel,
  SelectTrigger,
  SelectValue
} from '@/components/ui/select'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { useErrorStore } from '@/stores/error'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const storeError = useErrorStore()

const transactions = ref([])
const totalPages = ref(0)
const page = ref(1)
const perPage = 7

// Filters
const transactionType = ref('')
const startDate = ref('')
const endDate = ref('')
const username = ref('')

// Check if given dates are invalid
const isDateInvalid = computed(() => {
  return startDate.value && endDate.value && startDate.value > endDate.value
})

const fetchTransactionHistory = async () => {
  if (isDateInvalid.value) return
  storeError.resetMessages()
  try {
    const params = {
      page: page.value,
      per_page: perPage
    }
    if (transactionType.value && transactionType.value !== 'all') {
      params.type = transactionType.value
    }
    if (startDate.value) {
      params.start_date = startDate.value
    }
    if (endDate.value) {
      params.end_date = endDate.value
    }
    if (username.value) {
      params.user_name_like = username.value
    }
    const response = await axios.get('/transactions/history', { params })
    transactions.value = response.data.data
    totalPages.value = response.data.meta.last_page
  } catch (error) {
    console.error('Failed to fetch transaction history:', error)
  }
}

function handlePageChange(newPage) {
  if (newPage >= 1 && newPage <= totalPages.value) {
    page.value = newPage
    fetchTransactionHistory()
  }
}

onMounted(() => {
  fetchTransactionHistory()
})

// Debounce function to delay the search by name
function debounce(fn, delay) {
  let timeoutID
  return function (...args) {
    clearTimeout(timeoutID)
    timeoutID = setTimeout(() => {
      fn(...args)
    }, delay)
  }
}

// Watch for changes in the filters to refetch transactions
watch([transactionType, startDate, endDate], () => {
  page.value = 1
  fetchTransactionHistory()
})

// Watch for changes in the username with debounce
watch(
  username,
  debounce(() => {
    page.value = 1
    fetchTransactionHistory()
  }, 500)
)
</script>

<template>
  <div class="flex">
    <!-- Filters Section -->
    <div class="bg-white shadow rounded-lg p-6 w-1/4 mt-5">
      <h2 class="text-xl font-semibold mb-4">Filters</h2>
      <div class="grid grid-cols-1 gap-4">
        <div>
          <label for="transaction-type" class="block text-sm font-medium text-gray-700"
            >Filter by Transaction Type:</label
          >
          <Select v-model="transactionType">
            <SelectTrigger>
              <SelectValue :placeholder="'Select a transaction type'" />
            </SelectTrigger>
            <SelectContent>
              <SelectGroup>
                <SelectLabel>Select Transaction Type</SelectLabel>
                <SelectItem value="all">All</SelectItem>
                <SelectItem value="I">Internal</SelectItem>
                <SelectItem value="B">Bonus</SelectItem>
                <SelectItem value="P">Purchase</SelectItem>
              </SelectGroup>
            </SelectContent>
          </Select>
        </div>
        <div>
          <div v-if="authStore.user.type === 'A'" class="mb-5">
            <label for="username" class="block text-sm font-medium text-gray-700"
              >Search by Name:</label
            >
            <Input v-model="username" placeholder="Name..." />
          </div>
          <label for="start-date" class="block text-sm font-medium text-gray-700"
            >Start Date:</label
          >
          <input
            type="datetime-local"
            id="start-date"
            v-model="startDate"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
          />
        </div>
        <div>
          <label for="end-date" class="block text-sm font-medium text-gray-700">End Date:</label>
          <input
            type="datetime-local"
            id="end-date"
            v-model="endDate"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
          />
          <p v-if="isDateInvalid" class="text-red-500 mt-2">
            Start Date cannot be greater than End Date.
          </p>
        </div>
      </div>
    </div>

    <!-- Transactions Table Section -->
    <div class="flex-1 ml-5">
      <div class="pagination flex justify-center mb-3 mt-4">
        <Pagination
          :total="totalPages * perPage"
          :sibling-count="1"
          show-edges
          :default-page="page"
          @update:page="handlePageChange"
        >
          <PaginationList v-slot="{ items }" class="flex items-center gap-1">
            <PaginationFirst />
            <PaginationPrev />
            <template v-for="(item, index) in items">
              <PaginationListItem
                v-if="item.type === 'page'"
                :key="index"
                :value="item.value"
                as-child
              >
                <Button class="w-9 h-9 p-0" :variant="item.value === page ? 'default' : 'outline'">
                  {{ item.value }}
                </Button>
              </PaginationListItem>
              <PaginationEllipsis v-else :key="item.type" :index="index" />
            </template>

            <PaginationNext />
            <PaginationLast />
          </PaginationList>
        </Pagination>
      </div>
      <div v-if="transactions.length === 0" class="mt-6 ml-5">
        <p>No transactions found for the specified criteria :(</p>
      </div>
      <div v-else>
        <Table>
          <TableHeader>
        <TableRow>
          <TableHead>Date</TableHead>
          <TableHead v-if="authStore.user.type === 'A'" style="width: 150px"
            >User's Name</TableHead
          >
          <TableHead>Type</TableHead>
          <TableHead>Method</TableHead>
          <TableHead>Amount (€)</TableHead>
          <TableHead>Brain Coins</TableHead>
        </TableRow>
          </TableHeader>
          <TableBody>
        <TableRow v-for="transaction in transactions" :key="transaction.id">
          <TableCell style="width: 225px">{{
            new Date(transaction.transaction_datetime).toLocaleString()
          }}</TableCell>
          <TableCell v-if="authStore.user.type === 'A'" style="width: 275px">{{
            transaction.user_name
          }}</TableCell>
          <TableCell>{{ transaction.type }}</TableCell>
          <TableCell>{{ transaction.payment_type || '—' }}</TableCell>
          <TableCell>{{ transaction.euros ? transaction.euros + '€' : '—' }}</TableCell>
          <TableCell style="width: 150px">{{ transaction.brain_coins }} 🧠</TableCell>
        </TableRow>
          </TableBody>
        </Table>
      </div>
    </div>
  </div>
</template>
