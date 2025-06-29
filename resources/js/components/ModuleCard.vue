<template>
  <div
    :class="[
      'rounded-lg p-4 border relative',
      {
        'bg-gray-50 border-gray-200': hasAccess,
        'bg-gray-100 border-gray-300 opacity-75': !hasAccess,
        'cursor-move hover:bg-gray-100 transition-colors': draggable,
      }
    ]"
    :draggable="draggable"
    @dragstart="$emit('dragstart', $event)"
    @dragover.prevent
    @drop="$emit('drop', $event)"
  >
    <!-- Lock overlay for restricted modules -->
    <div v-if="!hasAccess && showAccessIndicator" class="absolute top-2 right-2">
      <svg 
        class="h-5 w-5 text-gray-400" 
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
    </div>
    
    <div class="flex justify-between items-start">
      <div class="flex-1">
        <!-- Drag handle for reorder mode -->
        <div v-if="showReorderHandle" class="flex items-center space-x-3 mb-2">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
              <path d="M7 2a1 1 0 000 2h6a1 1 0 100-2H7zM4 5a2 2 0 012-2h8a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 2a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h.01a1 1 0 100-2H10zm3 0a1 1 0 000 2h.01a1 1 0 100-2H13zm-6 3a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h.01a1 1 0 100-2H10zm3 0a1 1 0 000 2h.01a1 1 0 100-2H13z"/>
            </svg>
          </div>
          <span v-if="orderNumber" class="text-sm font-medium text-gray-500">#{{ orderNumber }}</span>
        </div>
        
        <!-- Module title (always clickable when clickable prop is true) -->
        <Link
          v-if="clickable && courseId"
          :href="route('courses.modules.show', [courseId, module.id])"
          class="font-medium text-blue-600 hover:text-blue-800 hover:underline"
        >
          {{ module.title }}
        </Link>
        <Link
          v-else-if="clickable && !courseId"
          :href="route('modules.show', module.id)"
          class="font-medium text-blue-600 hover:text-blue-800 hover:underline"
        >
          {{ module.title }}
        </Link>
        <h4 
          v-else 
          class="font-medium"
          :class="hasAccess ? 'text-gray-900' : 'text-gray-600'"
        >
          {{ module.title }}
        </h4>
        
        <p class="text-sm text-gray-600 mt-1">{{ module.description }}</p>
        
        <div class="flex items-center space-x-2 mt-2">
          <span
            :class="{
              'bg-green-100 text-green-800': module.difficulty === 'easy',
              'bg-yellow-100 text-yellow-800': module.difficulty === 'medium',
              'bg-red-100 text-red-800': module.difficulty === 'hard'
            }"
            class="inline-block px-2 py-1 text-xs font-medium rounded"
          >
            {{ module.difficulty }}
          </span>
          
          <!-- Module access indicator -->
          <span v-if="!hasAccess && showAccessIndicator && !module.is_free" class="text-xs text-gray-500">
            Video & downloads locked
          </span>
          <!-- Free module indicator -->
          <span v-if="module.is_free" class="text-xs text-green-600">
            Free Demo
          </span>
        </div>
      </div>
      
      <div class="flex items-center space-x-2">
        <div v-if="module.video_url" class="text-sm text-blue-600">
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
              d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" 
            />
          </svg>
        </div>
        
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'

interface Module {
  id: string
  title: string
  description: string
  difficulty: 'easy' | 'medium' | 'hard'
  video_url?: string
  is_free?: boolean
  pivot?: {
    order: number
  }
}

interface Props {
  module: Module
  hasAccess?: boolean
  clickable?: boolean
  draggable?: boolean
  showAccessIndicator?: boolean
  showReorderHandle?: boolean
  orderNumber?: number
  courseId?: string
}

withDefaults(defineProps<Props>(), {
  hasAccess: true,
  clickable: true,
  draggable: false,
  showAccessIndicator: true,
  showReorderHandle: false,
  orderNumber: undefined,
  courseId: undefined
})

defineEmits<{
  dragstart: [event: DragEvent]
  drop: [event: DragEvent]
}>()
</script>