<script setup>
import {
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card'
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group'

const invoices = [
    {
        invoice: 'INV001',
        paymentStatus: 'Paid',
        totalAmount: '$250.00',
        paymentMethod: 'Credit Card',
    },
    {
        invoice: 'INV002',
        paymentStatus: 'Pending',
        totalAmount: '$150.00',
        paymentMethod: 'PayPal',
    },
]
</script>

<template>
    <div style="display: flex; gap: 1rem; align-items: flex-start;">
        <!-- Card Section -->
        <div style="flex: 1; max-width: 300px;">
            <Card>
                <CardHeader>
                    <CardTitle>Card Title</CardTitle>
                    <CardDescription>Card Description</CardDescription>
                </CardHeader>
                <CardContent>
                    Card Content
                </CardContent>
                <CardFooter>
                    Card Footer
                </CardFooter>
            </Card>
        </div>

        <!-- Table Section -->
        <div style="flex: 2; flex-grow: 1;">
            <!-- Select and RadioGroup Side by Side -->
            <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                <!-- Select -->           
                <div style="flex-grow: 1; max-width: 200px;">
                    <Select>
                        <SelectTrigger>
                            <SelectValue placeholder="Select a board" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectLabel>Fruits</SelectLabel>
                                <SelectItem value="apple">Apple</SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                </div>
                
                <!-- RadioGroup -->
                <div>
                    <RadioGroup default-value="option-one"  style="display: flex">
                        <div class="flex items-center space-x-2">
                            <RadioGroupItem id="option-one" value="option-one" />
                            <Label for="option-one">Best Time</Label>
                        </div>
                        <div class="flex items-center space-x-2">
                            <RadioGroupItem id="option-two" value="option-two" />
                            <Label for="option-two">Minimum Turns</Label>
                        </div>
                    </RadioGroup>
                </div>
            </div>

            <!-- Table Section -->
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Invoice</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Method</TableHead>
                        <TableHead class="text-right">Amount</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="invoice in invoices" :key="invoice.invoice">
                        <TableCell>{{ invoice.invoice }}</TableCell>
                        <TableCell>{{ invoice.paymentStatus }}</TableCell>
                        <TableCell>{{ invoice.paymentMethod }}</TableCell>
                        <TableCell class="text-right">{{ invoice.totalAmount }}</TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </div>
</template>