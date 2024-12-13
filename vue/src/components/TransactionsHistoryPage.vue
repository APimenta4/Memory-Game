<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';

import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';

const transactions = ref([]);

async function fetchTransactionHistory() {
  try {
    const response = await axios.get('/transactions/history');
    transactions.value = response.data;
  } catch (error) {
    console.error('Failed to fetch transaction history:', error);
  }
}

onMounted(() => {
  fetchTransactionHistory();
});
</script>

<template>
  <Table>
    <TableCaption>Your transaction history</TableCaption>
    <TableHeader>
      <TableRow>
        <TableHead>Date</TableHead>
        <TableHead>Type</TableHead>
        <TableHead>Method</TableHead>
        <TableHead>Amount (€)</TableHead>
        <TableHead>Brain Coins</TableHead>
      </TableRow>
    </TableHeader>
    <TableBody>
      <TableRow
        v-for="(transaction, index) in transactions"
        :key="transaction.id"
        :class="index % 2 === 0 ? 'even-row' : 'odd-row'">
        <TableCell>{{ new Date(transaction.transaction_datetime).toLocaleString() }}</TableCell>
        <TableCell>
          {{ transaction.type === 'P' ? 'Purchase' : 'Bonus/Spending' }}
        </TableCell>
        <TableCell>{{ transaction.payment_type }}</TableCell>
        <TableCell>{{ transaction.euros || '-' }}</TableCell>
        <TableCell>{{ transaction.brain_coins }}</TableCell>
      </TableRow>
    </TableBody>
  </Table>
</template>

<style scoped>
.table {
  margin-top: 20px;
  width: 100%;
}

.even-row {
  background-color: #F0FFFF;
  color: rgb(0, 0, 0);
}

.odd-row {
  background-color: #E0FFFF; 
  color: rgb(0, 0, 0);
}
</style>
