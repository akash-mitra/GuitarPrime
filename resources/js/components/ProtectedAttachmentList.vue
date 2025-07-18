<template>
    <div>
        <h3 class="mb-2 text-lg font-medium">Attachments</h3>

        <div v-if="attachments && attachments.length > 0" class="space-y-2">
            <div
                v-for="attachment in attachments"
                :key="attachment.id"
                class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-800 dark:bg-gray-900/50"
            >
                <div class="flex-1">
                    <h4 class="text-sm font-medium dark:text-gray-100">{{ attachment.name }}</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ formatFileSize(attachment.size) }} • {{ attachment.mime_type }}</p>
                </div>

                <!-- Show download button if user has access -->
                <button
                    v-if="canAccess"
                    @click="downloadAttachment(attachment)"
                    class="flex items-center space-x-1 text-sm text-blue-600 hover:text-blue-800"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                        />
                    </svg>
                    <span>Download</span>
                </button>

                <!-- Show purchase button if user doesn't have access -->
                <div v-else class="flex items-center space-x-2">
                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                        />
                    </svg>

                    <PurchaseButton
                        :purchasable-type="purchasableType"
                        :purchasable-id="purchasableId"
                        :price="pricing.price"
                        :is-free="pricing.is_free"
                        class="px-2 py-1 text-xs"
                    />
                </div>
            </div>
        </div>

        <div v-else class="py-8 text-center">
            <div class="mb-4 text-gray-400">
                <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"
                    />
                </svg>
            </div>
            <p class="text-gray-500">This module doesn't have any attachments yet.</p>
        </div>
    </div>
</template>

<script setup lang="ts">
import PurchaseButton from '@/components/PurchaseButton.vue';

interface Attachment {
    id: string;
    name: string;
    filename: string;
    size: number;
    mime_type: string;
}

interface Props {
    attachments?: Attachment[];
    canAccess: boolean;
    purchasableType: 'course' | 'module';
    purchasableId: string;
    pricing: {
        price?: number;
        is_free: boolean;
        formatted_price: string;
    };
}

defineProps<Props>();

const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const downloadAttachment = (attachment: Attachment) => {
    // TODO: Implement actual download logic
    // This would typically make a request to a protected download endpoint
    console.log('Downloading attachment:', attachment.name);

    // For now, we'll just alert the user
    alert(`Downloading ${attachment.name}...`);
};
</script>
