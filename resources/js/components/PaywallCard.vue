<template>
    <Card class="border-2 border-dashed border-gray-300 bg-gradient-to-br from-gray-50 to-gray-100">
        <CardContent class="py-8 text-center">
            <div class="mb-4">
                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.5"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                    />
                </svg>
            </div>

            <h3 class="mb-2 text-xl font-semibold text-gray-900 dark:text-gray-300">Premium Content</h3>

            <p class="mx-auto mb-6 max-w-md text-gray-500">
                {{ description }}
            </p>

            <div class="space-y-4">
                <div v-if="pricing.formatted_price" class="text-3xl font-bold text-gray-900 dark:text-gray-400">
                    {{ pricing.formatted_price }}
                </div>

                <div class="space-y-2">
                    <slot name="purchase-button">
                        <PurchaseButton
                            :purchasable-type="purchasableType"
                            :purchasable-id="purchasableId"
                            :price="pricing.price"
                            :is-free="pricing.is_free"
                            :class="primaryButtonClass"
                        />
                    </slot>

                    <p class="mt-1 text-xs text-gray-500">One-time purchase • Lifetime access</p>
                </div>
            </div>
        </CardContent>
    </Card>
</template>

<script setup lang="ts">
import PurchaseButton from '@/components/PurchaseButton.vue';
import { Card, CardContent } from '@/components/ui/card';

interface Props {
    description: string;
    purchasableType: 'course' | 'module';
    purchasableId: string;
    pricing: {
        price?: number;
        is_free: boolean;
        formatted_price: string;
    };
    primaryButtonClass?: string;
}

defineProps<Props>();
</script>
