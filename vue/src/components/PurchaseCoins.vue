<script setup>
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger
} from '@/components/ui/dialog'
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle
} from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue
} from '@/components/ui/select'
import {
  NumberField,
  NumberFieldContent,
  NumberFieldDecrement,
  NumberFieldIncrement,
  NumberFieldInput
} from '@/components/ui/number-field'
import { Button } from '@/components/ui/button'
import { inject, ref } from "vue";
import { useAuthStore } from "@/stores/auth";
import axios from "axios";

// Remove showBuyCoinsDialog ref
const paymentType = ref(""); // No .value in v-model
const paymentReference = ref("");
const value = ref(1);
const successMessage = ref("");
const errorMessage = ref("");
const socket = inject('socket')
const storeAuth = useAuthStore()
// Function to validate payment details
function validatePayment(paymentType, paymentReference, value) {
  // Check if payment type, reference, or value is missing
  if (!paymentType || !paymentReference || !value) {
    return "Payment type, reference, and value are required.";
  }

  // Validate payment type
  const validTypes = ["MBWAY", "PAYPAL", "IBAN", "MB", "VISA"];
  if (!validTypes.includes(paymentType)) {
    return "Invalid payment type. Accepted types are: MBWAY, PAYPAL, IBAN, MB, VISA.";
  }

  // Validate reference based on payment type
  if (paymentType === "MBWAY" && !/^[9]\d{8}$/.test(paymentReference)) {
    return "Invalid MBWAY reference. It should be 9 digits starting with '9'.";
  }
  if (paymentType === "PAYPAL" && !/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(paymentReference)) {
    return "Invalid PayPal email. Please provide a valid email address.";
  }
  if (paymentType === "IBAN" && !/^[A-Z]{2}\d{23}$/.test(paymentReference)) {
    return "Invalid IBAN reference. It should start with two letters followed by 23 digits.";
  }
  if (paymentType === "MB" && !/^\d{5}-\d{9}$/.test(paymentReference)) {
    return "Invalid MB reference. It should be in the format 'xxxxx-xxxxxxxxx'.";
  }
  if (paymentType === "VISA" && !/^[4]\d{15}$/.test(paymentReference)) {
    return "Invalid VISA reference. It should be 16 digits starting with '4'.";
  }

  // Validate value
  if (value <= 0 || value >= 100 || !Number.isInteger(value)) {
    return "Invalid value. The value must be a positive integer greater than 0 and less than 100.";
  }

  // Invalid reference rules (already provided by the external service)
  if (paymentType === "MBWAY" && paymentReference.startsWith("90")) {
    return "The MBWay phone number is invalid. MBWay phone numbers cannot start with '90'.";
  }
  if (paymentType === "PAYPAL" && paymentReference.startsWith("xx")) {
    return "The PayPal email is invalid. PayPal emails cannot start with 'xx'.";
  }
  if (paymentType === "IBAN" && paymentReference.startsWith("XX")) {
    return "The provided IBAN is invalid. IBAN references cannot start with 'XX'.";
  }
  if (paymentType === "MB" && paymentReference.startsWith("9")) {
    return "The MB entity number is invalid. MB entity numbers cannot start with '9'.";
  }
  if (paymentType === "VISA" && paymentReference.startsWith("40")) {
    return "The VISA reference number is invalid. VISA references cannot start with '40'.";
  }

  // Insufficient funds rules (already provided by the external service)
  const limits = {
    MBWAY: 5,
    PAYPAL: 10,
    IBAN: 50,
    MB: 20,
    VISA: 30,
  };

  if (value > limits[paymentType]) {
    return `Your ${paymentType} account has a limit of ${limits[paymentType]}€. Please check your available balance before making the purchase.`;
  }

  return ""; // Returns an empty string if there are no errors
}

async function buyCoins() {
  // Validate payment before proceeding
  const validationError = validatePayment(paymentType.value, paymentReference.value, value.value);
  if (validationError) {
    errorMessage.value = validationError;
    successMessage.value = "";
    return;
  }

  // Simulate the request to buy coins (this part can be adjusted according to your real API)
  try {
    const response = await axios.post("/transactions/buy-coins", {
      payment_type: paymentType.value,
      payment_reference: paymentReference.value,
      value: value.value,
    });
    socket.emit('notification_alert', storeAuth.user.id)
    successMessage.value = `Purchase successful! You received ${response.data.brain_coins} brain coins.`;
    errorMessage.value = "";
    
  } catch (error) {
    successMessage.value = "";
    errorMessage.value = error.response?.data?.error || "Purchase error.";
  }
}
</script>
<template>
  <Dialog>
    <DialogTrigger as-child>
      <Button variant="danger" class="bg-red-600 text-white">
        Buy More Brains
      </Button>
    </DialogTrigger>
    <DialogContent>
      <DialogHeader>
        <DialogTitle></DialogTitle>
        <DialogDescription> </DialogDescription>
      </DialogHeader>
      <Card class="max-w-md mx-auto">
        <CardHeader>
          <CardTitle>Buy Brain Coins</CardTitle>
          <CardDescription>
            Choose the payment method, enter the reference, and the amount.
          </CardDescription>
        </CardHeader>

        <CardContent>
          <form @submit.prevent="buyCoins" class="grid gap-4">
            <!-- Payment Method -->
            <div class="flex flex-col space-y-1.5">
              <Label for="paymentType">Payment Method</Label>
              <Select v-model="paymentType">
                <SelectTrigger id="paymentType">
                  <SelectValue placeholder="Select" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="MBWAY">MBWAY</SelectItem>
                  <SelectItem value="PAYPAL">PayPal</SelectItem>
                  <SelectItem value="IBAN">IBAN</SelectItem>
                  <SelectItem value="MB">MB</SelectItem>
                  <SelectItem value="VISA">Visa</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <!-- Payment Reference -->
            <div class="flex flex-col space-y-1.5">
              <Label for="paymentReference">Reference</Label>
              <Input
                id="paymentReference"
                v-model="paymentReference"
                placeholder="Enter reference"
              />
            </div>

            <!-- Amount -->
            <div class="flex flex-col space-y-1.5">
              <Label for="value">Amount (€):</Label>
              <NumberField v-model="value" :min="1" :max="99999" required>
                <NumberFieldContent>
                  <NumberFieldDecrement>-</NumberFieldDecrement>
                  <NumberFieldInput />
                  <NumberFieldIncrement>+</NumberFieldIncrement>
                </NumberFieldContent>
              </NumberField>
            </div>
          </form>
        </CardContent>

        <CardFooter class="flex flex-col gap-4">
          <div class="flex space-x-4">
            <Button class="w-32 bg-blue-600 text-white hover:bg-blue-700" @click="buyCoins"
              >Buy</Button
            >
            <DialogTrigger as-child>
              <Button
                class="w-32 bg-red-600 text-white hover:bg-red-700"
                >Cancel</Button>
            </DialogTrigger>
            
          </div>
          <p v-if="successMessage" class="text-green-600">{{ successMessage }}</p>
          <p v-if="errorMessage" class="text-red-600">{{ errorMessage }}</p>
        </CardFooter>
      </Card>
      <DialogFooter> </DialogFooter>
    </DialogContent>
  </Dialog>
</template>