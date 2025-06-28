<template>
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
        <div class="flex gap-4">
            <div class="flex-shrink-0">
                <img
                    v-if="theme.cover_image"
                    :src="theme.cover_image"
                    :alt="theme.name"
                    class="w-16 h-16 object-cover rounded-lg"
                />
                <div
                    v-else
                    class="w-16 h-16 bg-gray-200 dark:bg-gray-600 rounded-lg flex items-center justify-center"
                >
                    <span class="text-gray-400 dark:text-gray-300 text-xs">No Image</span>
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <Link
                    :href="route('themes.show', theme.id)"
                    class="font-semibold text-lg mb-2 text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition-colors block"
                >
                    {{ theme.name }}
                </Link>
                <p class="text-gray-600 dark:text-gray-300 text-sm mb-3 line-clamp-3">
                    {{ theme.description }}
                </p>
                <div class="flex justify-between items-center">
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        {{ theme.courses_count }} courses
                    </span>
                    <div v-if="canEdit || canDelete" class="flex space-x-2">
                        <Link
                            v-if="canEdit"
                            :href="route('themes.edit', theme.id)"
                            class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 text-sm"
                        >
                            Edit
                        </Link>
                        <button
                            v-if="canDelete"
                            @click="$emit('delete', theme)"
                            class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 text-sm"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

defineProps<{
    theme: {
        id: number
        name: string
        description: string
        courses_count: number
        cover_image?: string
    }
}>()

defineEmits<{
    delete: [theme: any]
}>()

const page = usePage()

const canEdit = computed(() => {
    return page.props.auth.user.role !== 'student'
})

const canDelete = computed(() => {
    return page.props.auth.user.role === 'admin'
})
</script>
