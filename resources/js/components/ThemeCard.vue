<template>
    <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
        <h4 class="font-semibold text-lg mb-2">{{ theme.name }}</h4>
        <p class="text-gray-600 text-sm mb-3 line-clamp-3">
            {{ theme.description }}
        </p>
        <div class="flex justify-between items-center">
            <span class="text-xs text-gray-500">
                {{ theme.courses_count }} courses
            </span>
            <div class="flex space-x-2">
                <Link
                    :href="route('themes.show', theme.id)"
                    class="text-blue-600 hover:text-blue-800 text-sm"
                >
                    View
                </Link>
                <Link
                    v-if="canEdit"
                    :href="route('themes.edit', theme.id)"
                    class="text-green-600 hover:text-green-800 text-sm"
                >
                    Edit
                </Link>
                <button
                    v-if="canDelete"
                    @click="$emit('delete', theme)"
                    class="text-red-600 hover:text-red-800 text-sm"
                >
                    Delete
                </button>
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