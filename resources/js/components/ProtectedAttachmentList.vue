<template>
  <div v-if="attachments && attachments.length > 0" class="mt-8">
    <h3 class="text-lg font-semibold mb-4">Attachments</h3>
    <div class="space-y-2">
      <div
        v-for="attachment in attachments"
        :key="attachment.id"
        class="bg-gray-50 rounded-lg p-3 border border-gray-200 flex justify-between items-center"
      >
        <div class="flex-1">
          <h4 class="font-medium text-sm">{{ attachment.filename }}</h4>
          <p class="text-xs text-gray-500">
            {{ formatFileSize(attachment.size) }} • {{ attachment.mime_type }}
          </p>
        </div>
        
        <!-- Show download button if user has access -->
        <button 
          v-if="canAccess"
          @click="downloadAttachment(attachment)"
          class="text-blue-600 hover:text-blue-800 text-sm flex items-center space-x-1"
        >
          <svg 
            class="h-4 w-4" 
            fill="none" 
            viewBox="0 0 24 24" 
            stroke="currentColor"
          >
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
          <svg 
            class="h-4 w-4 text-gray-400" 
            fill="none" 
            viewBox="0 0 24 24" 
            stroke="currentColor"
          >
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
            class="text-xs py-1 px-2"
          />
        </div>
      </div>
    </div>
  </div>

  <div v-else class="mt-8 text-center py-8">
    <div class="text-gray-400 mb-4">
      <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path 
          stroke-linecap="round" 
          stroke-linejoin="round" 
          stroke-width="2" 
          d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" 
        />
      </svg>
    </div>
    <h3 class="text-lg font-medium text-gray-900 mb-2">No attachments</h3>
    <p class="text-gray-500">This {{ purchasableType }} doesn't have any attachments yet.</p>
  </div>
</template>

<script setup lang="ts">
import PurchaseButton from '@/components/PurchaseButton.vue'

interface Attachment {
  id: string
  filename: string
  size: number
  mime_type: string
}

interface Props {
  attachments?: Attachment[]
  canAccess: boolean
  purchasableType: 'course' | 'module'
  purchasableId: string
  pricing: {
    price?: number
    is_free: boolean
    formatted_price: string
  }
}

defineProps<Props>()

const formatFileSize = (bytes: number): string => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const downloadAttachment = (attachment: Attachment) => {
  // TODO: Implement actual download logic
  // This would typically make a request to a protected download endpoint
  console.log('Downloading attachment:', attachment.filename)
  
  // For now, we'll just alert the user
  alert(`Downloading ${attachment.filename}...`)
}
</script>