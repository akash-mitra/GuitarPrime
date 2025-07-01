<template>
  <Button
    @click="handlePurchase"
    :disabled="loading"
    :variant="isFree ? 'secondary' : 'default'"
    :class="buttonClass"
  >
    <template v-if="loading">
      <svg
        class="animate-spin -ml-1 mr-2 h-4 w-4"
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
      >
        <circle
          class="opacity-25"
          cx="12"
          cy="12"
          r="10"
          stroke="currentColor"
          stroke-width="4"
        />
        <path
          class="opacity-75"
          fill="currentColor"
          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
        />
      </svg>
      Processing...
    </template>

    <template v-else>
      <svg
        v-if="isFree"
        class="mr-2 h-4 w-4"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M13 10V3L4 14h7v7l9-11h-7z"
        />
      </svg>

      <svg
        v-else
        class="mr-2 h-4 w-4"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
        />
      </svg>

      {{ buttonText }}
    </template>
  </Button>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'

interface Props {
  purchasableType: 'course' | 'module'
  purchasableId: string
  price?: number | string
  isFree: boolean
  class?: string
}

const props = defineProps<Props>()

const loading = ref(false)

const buttonText = computed(() => {
  if (props.isFree) {
    return 'Access Free Content'
  }

  // Convert price to number and format it in INR
  const numericPrice = typeof props.price === 'string' ? parseFloat(props.price) : props.price
  // Convert paisa to rupees and format
  const priceInRupees = (numericPrice || 0) / 100
  const formattedPrice = priceInRupees.toFixed(2)

  return `Purchase for ₹${formattedPrice}`
})

const buttonClass = computed(() => props.class || '')

const handlePurchase = async () => {
  if (loading.value) return

  loading.value = true

  try {
    if (props.isFree) {
      // For free content, just refresh the page to update access
      router.reload()
    } else {
      // For paid content, redirect to purchase flow
      router.post(route('purchases.store'), {
        type: props.purchasableType,
        id: props.purchasableId,
        payment_provider: 'stripe', // Default to Stripe for now
      })
    }
  } catch (error) {
    console.error('Purchase failed:', error)
    // Error handling is managed by Inertia - validation errors will show up in the form
  } finally {
    loading.value = false
  }
}
</script>
