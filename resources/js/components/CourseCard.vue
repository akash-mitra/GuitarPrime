<template>
    <Link
        :href="route('courses.show', course.id)"
        class="group block overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg transition-all duration-300 hover:shadow-xl dark:border-gray-700 dark:bg-gray-800"
    >
        <!-- Image Section with Overlay -->
        <div class="relative aspect-video overflow-hidden bg-gray-100 dark:bg-gray-700">
            <img
                v-if="course.cover_image"
                :src="course.cover_image"
                :alt="course.title"
                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
            />
            <div
                v-else
                class="flex h-full w-full items-center justify-center bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-600 dark:to-gray-700"
            >
                <span class="text-lg font-medium text-gray-500 dark:text-gray-400">No Image</span>
            </div>

            <!-- Status Labels - Top Right -->
            <div class="absolute top-3 right-3 flex flex-col gap-2">
                <!-- Pending Approval Label -->
                <span
                    v-if="!course.is_approved"
                    class="inline-flex items-center rounded-full bg-yellow-100/90 px-2.5 py-0.5 text-xs font-medium text-yellow-800 backdrop-blur-sm dark:bg-yellow-900/50 dark:text-yellow-200"
                >
                    Pending
                </span>

                <!-- Free Label -->
                <span
                    v-if="course.price === 0 || course.price === null"
                    class="inline-flex items-center rounded-full bg-green-100/90 px-2.5 py-0.5 text-lg font-medium text-green-800 backdrop-blur-sm dark:bg-green-900/50 dark:text-green-200"
                >
                    Free
                </span>

                <!-- Price Label -->
                <span
                    v-else
                    class="inline-flex items-center rounded-full bg-blue-100/90 px-2.5 py-0.5 text-xs font-medium text-blue-800 backdrop-blur-sm dark:bg-blue-900/50 dark:text-blue-200"
                >
                    {{ formatPrice(course.price) }}
                </span>
            </div>

            <!-- Glassmorphic Title Overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent">
                <div class="absolute right-0 bottom-0 left-0 bg-black/30 p-4 backdrop-blur-sm">
                    <h3 class="text-xl font-bold text-white transition-colors group-hover:text-blue-200">
                        {{ course.title }}
                    </h3>
                </div>
            </div>
        </div>

        <!-- Content Section -->
        <div class="p-6">
            <p class="mb-4 line-clamp-3 text-sm text-gray-600 dark:text-gray-300">
                {{ course.description }}
            </p>

            <!-- Course Info -->
            <div class="mb-4 space-y-2">
                <div class="flex items-center space-x-2">
<!--                    <span class="text-xs text-gray-500 dark:text-gray-400">Theme:</span>-->
                    <span
                        class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-300"
                    >
                        {{ course.theme.name }}
                    </span>
                </div>
            </div>

            <!-- Actions -->
            <div v-if="canEdit || canDelete" class="flex justify-end space-x-2">
                <Link
                    v-if="canEdit"
                    :href="route('courses.edit', course.id)"
                    @click.stop
                    class="rounded-lg bg-green-50 px-3 py-1 text-sm font-medium text-green-700 transition-colors hover:bg-green-100 dark:bg-green-900/30 dark:text-green-400 dark:hover:bg-green-900/50"
                >
                    Edit
                </Link>
                <button
                    v-if="canDelete"
                    @click.stop="$emit('delete', course)"
                    class="rounded-lg bg-red-50 px-3 py-1 text-sm font-medium text-red-700 transition-colors hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50"
                >
                    Delete
                </button>
            </div>
        </div>
        <div class="px-6 py-4 bg-gray-50 border-t dark:border-gray-700 dark:bg-gray-900/80 rounded-b-xl">
            <div class="flex items-center space-x-2">
                <img
                    v-if="course.coach.avatar"
                    :src="course.coach.avatar"
                    :alt="course.coach.name"
                    class="h-6 w-6 rounded-full object-cover"
                />
                <div
                    v-else
                    class="h-6 w-6 rounded-full bg-gray-300 flex items-center justify-center dark:bg-gray-600"
                >
                            <span class="text-sm text-gray-600 dark:text-gray-300">
                                {{ course.coach.name.charAt(0).toUpperCase() }}
                            </span>
                </div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ course.coach.name }}
                        </span>
            </div>
        </div>
    </Link>
</template>

<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
    avatar?: string;
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
    price?: number | null;
    is_free?: boolean;
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

const formatPrice = (priceInPaisa: number | null | undefined): string => {
    if (!priceInPaisa || priceInPaisa <= 0) {
        return 'Free';
    }

    // Convert paisa to rupees and format in INR
    const priceInRupees = priceInPaisa / 100;
    return `₹${priceInRupees.toFixed(2)}`;
};
</script>
