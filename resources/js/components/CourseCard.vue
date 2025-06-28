<template>
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
        <div class="flex gap-4">
            <div class="flex-shrink-0">
                <img
                    v-if="course.cover_image"
                    :src="course.cover_image"
                    :alt="course.title"
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
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <div class="flex items-center space-x-3 mb-2">
                            <Link
                                :href="route('courses.show', course.id)"
                                class="font-semibold text-lg text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                            >
                                {{ course.title }}
                            </Link>
                            <span
                                :class="{
                                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': course.is_approved,
                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': !course.is_approved
                                }"
                                class="px-2 py-1 text-xs font-medium rounded"
                            >
                                {{ course.is_approved ? 'Approved' : 'Pending' }}
                            </span>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300 text-sm mb-2">{{ course.description }}</p>
                        <div class="text-xs text-gray-500 dark:text-gray-400 space-y-1">
                            <p>Theme: {{ course.theme.name }}</p>
                            <p>Coach: {{ course.coach.name }}</p>
                        </div>
                    </div>
                    <div v-if="canEdit || canDelete" class="flex space-x-2 ml-4">
                        <Link
                            v-if="canEdit"
                            :href="route('courses.edit', course.id)"
                            class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 text-sm"
                        >
                            Edit
                        </Link>
                        <button
                            v-if="canDelete"
                            @click="$emit('delete', course)"
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

interface User {
    id: number
    name: string
    email: string
    role: string
}

interface Theme {
    id: string
    name: string
}

interface Course {
    id: string
    title: string
    description: string
    is_approved: boolean
    coach_id: number
    cover_image?: string
    theme: Theme
    coach: User
}

const props = defineProps<{
    course: Course
}>()

defineEmits<{
    delete: [course: Course]
}>()

const page = usePage()

const canEdit = computed(() => {
    const user = page.props.auth.user as User
    return user.role === 'admin' || (user.role === 'coach' && props.course.coach_id === user.id)
})

const canDelete = computed(() => {
    const user = page.props.auth.user as User
    return user.role === 'admin' || (user.role === 'coach' && props.course.coach_id === user.id && !props.course.is_approved)
})
</script>
