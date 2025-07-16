<template>
    <div
        :class="[
            'relative rounded-2xl border p-6 transition-all duration-300',
            {
                'backdrop-blur-sm bg-white/10 dark:bg-gray-950 border-white/20 dark:border-white/10 shadow hover:shadow-2xl hover:bg-white/20 dark:hover:bg-gray-900': hasAccess,
                'backdrop-blur-sm bg-white/5 dark:bg-black/10 border-white/10 dark:border-white/5 shadow-lg opacity-75': !hasAccess,
                'cursor-move transition-all hover:scale-[1.02]': draggable,
            },
        ]"
        :draggable="draggable"
        @dragstart="$emit('dragstart', $event)"
        @dragover.prevent
        @drop="$emit('drop', $event)"
    >
        <!-- Lock overlay for restricted modules -->
        <div v-if="!hasAccess && showAccessIndicator" class="absolute top-4 right-4">
            <div class="backdrop-blur-sm bg-white/20 dark:bg-black/30 rounded-full p-2 border border-white/30 dark:border-white/20">
                <svg class="h-5 w-5 text-gray-600 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                    />
                </svg>
            </div>
        </div>

        <div class="flex items-start justify-between">
            <div class="flex-1">
                <!-- Drag handle for reorder mode -->
                <div v-if="showReorderHandle" class="mb-3 flex items-center space-x-3">
                    <div class="flex-shrink-0 backdrop-blur-sm bg-white/20 dark:bg-black/30 p-2 rounded-lg border border-white/30 dark:border-white/20">
                        <svg class="h-5 w-5 text-gray-600 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M7 2a1 1 0 000 2h6a1 1 0 100-2H7zM4 5a2 2 0 012-2h8a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 2a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h.01a1 1 0 100-2H10zm3 0a1 1 0 000 2h.01a1 1 0 100-2H13zm-6 3a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h.01a1 1 0 100-2H10zm3 0a1 1 0 000 2h.01a1 1 0 100-2H13z"
                            />
                        </svg>
                    </div>
                    <span v-if="orderNumber" class="text-sm font-medium text-gray-600 dark:text-gray-400 bg-white/20 dark:bg-black/30 px-3 py-1 rounded-full backdrop-blur-sm">#{{ orderNumber }}</span>
                </div>

                <!-- Module title (always clickable when clickable prop is true) -->
                <Link
                    v-if="clickable && courseId"
                    :href="route('courses.modules.show', [courseId, module.id])"
                    class="text-lg font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline transition-colors duration-200"
                >
                    {{ module.title }}
                </Link>
                <Link
                    v-else-if="clickable && !courseId"
                    :href="route('modules.show', module.id)"
                    class="text-lg font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline transition-colors duration-200"
                >
                    {{ module.title }}
                </Link>
                <h4 v-else class="text-lg font-semibold" :class="hasAccess ? 'text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400'">
                    {{ module.title }}
                </h4>

                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300 leading-relaxed">{{ module.description }}</p>

                <div class="mt-4 flex items-center space-x-3">
                    <span
                        :class="{
                            'bg-green-500/20 text-green-100 border-green-400/30': module.difficulty === 'easy',
                            'bg-yellow-500/20 text-yellow-100 border-yellow-400/30': module.difficulty === 'medium',
                            'bg-red-500/20 text-red-100 border-red-400/30': module.difficulty === 'hard',
                        }"
                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium backdrop-blur-sm border"
                    >
                        {{ module.difficulty }}
                    </span>

                    <!-- Module access indicator -->
                    <span v-if="!hasAccess && showAccessIndicator && !module.is_free" class="text-xs text-gray-500 dark:text-gray-400 bg-gray-200/20 dark:bg-gray-700/20 px-2 py-1 rounded-full backdrop-blur-sm">
                        🔒 Locked Content
                    </span>
                    <!-- Free module indicator -->
                    <span v-if="module.is_free" class="text-xs text-green-600 dark:text-green-400 bg-green-200/20 dark:bg-green-700/20 px-2 py-1 rounded-full backdrop-blur-sm">
                        ✨ Free Demo
                    </span>
                </div>
            </div>

            <div class="flex items-center space-x-2">
                <div v-if="module.video_url" class="backdrop-blur-sm bg-blue-500/20 text-blue-600 dark:text-blue-400 p-2 rounded-full border border-blue-400/30">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
import { Link } from '@inertiajs/vue3';

interface Module {
    id: string;
    title: string;
    description: string;
    difficulty: 'easy' | 'medium' | 'hard';
    video_url?: string;
    is_free?: boolean;
    pivot?: {
        order: number;
    };
}

interface Props {
    module: Module;
    hasAccess?: boolean;
    clickable?: boolean;
    draggable?: boolean;
    showAccessIndicator?: boolean;
    showReorderHandle?: boolean;
    orderNumber?: number;
    courseId?: string;
}

withDefaults(defineProps<Props>(), {
    hasAccess: true,
    clickable: true,
    draggable: false,
    showAccessIndicator: true,
    showReorderHandle: false,
    orderNumber: undefined,
    courseId: undefined,
});

defineEmits<{
    dragstart: [event: DragEvent];
    drop: [event: DragEvent];
}>();
</script>
