<template>
    <div class="rounded-lg border border-gray-200 bg-gray-50 p-6 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex gap-4">
            <div class="flex-shrink-0">
                <img v-if="course.cover_image" :src="course.cover_image" :alt="course.title" class="h-16 w-16 rounded-lg object-cover" />
                <div v-else class="flex h-16 w-16 items-center justify-center rounded-lg bg-gray-200 dark:bg-gray-600">
                    <span class="text-xs text-gray-400 dark:text-gray-300">No Image</span>
                </div>
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="mb-2 flex items-center space-x-3">
                            <Link
                                :href="route('courses.show', course.id)"
                                class="text-lg font-semibold text-gray-900 transition-colors hover:text-blue-600 dark:text-white dark:hover:text-blue-400"
                            >
                                {{ course.title }}
                            </Link>
                            <span
                                :class="{
                                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': course.is_approved,
                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': !course.is_approved,
                                }"
                                class="rounded px-2 py-1 text-xs font-medium"
                            >
                                {{ course.is_approved ? 'Approved' : 'Pending' }}
                            </span>
                        </div>
                        <p class="mb-2 text-sm text-gray-600 dark:text-gray-300">{{ course.description }}</p>
                        <div class="space-y-1 text-xs text-gray-500 dark:text-gray-400">
                            <p>Theme: {{ course.theme.name }}</p>
                            <p>Coach: {{ course.coach.name }}</p>
                        </div>
                    </div>
                    <div v-if="canEdit || canDelete" class="ml-4 flex space-x-2">
                        <Link
                            v-if="canEdit"
                            :href="route('courses.edit', course.id)"
                            class="text-sm text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300"
                        >
                            Edit
                        </Link>
                        <button
                            v-if="canDelete"
                            @click="$emit('delete', course)"
                            class="text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
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
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
}

interface Theme {
    id: string;
    name: string;
}

interface Course {
    id: string;
    title: string;
    description: string;
    is_approved: boolean;
    coach_id: number;
    cover_image?: string;
    theme: Theme;
    coach: User;
}

const props = defineProps<{
    course: Course;
}>();

defineEmits<{
    delete: [course: Course];
}>();

const page = usePage();

const canEdit = computed(() => {
    const user = page.props.auth.user as User;
    return user.role === 'admin' || (user.role === 'coach' && props.course.coach_id === user.id);
});

const canDelete = computed(() => {
    const user = page.props.auth.user as User;
    return user.role === 'admin' || (user.role === 'coach' && props.course.coach_id === user.id && !props.course.is_approved);
});
</script>
