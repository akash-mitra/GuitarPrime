<template>
    <Link
        :href="route('themes.show', theme.id)"
        class="group block overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg transition-all duration-300 hover:shadow-xl dark:border-gray-700 dark:bg-gray-800"
    >
        <!-- Image Section with Overlay -->
        <div class="relative aspect-video overflow-hidden bg-gray-100 dark:bg-gray-700">
            <img
                v-if="theme.cover_image"
                :src="theme.cover_image"
                :alt="theme.name"
                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
            />
            <div
                v-else
                class="flex h-full w-full items-center justify-center bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-600 dark:to-gray-700"
            >
                <span class="text-lg font-medium text-gray-500 dark:text-gray-400">No Image</span>
            </div>

            <!-- Glassmorphic Title Overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent">
                <div class="absolute right-0 bottom-0 left-0 bg-black/30 p-4 backdrop-blur-sm">
                    <h3 class="text-xl font-bold text-white transition-colors group-hover:text-orange-300">
                        {{ theme.name }}
                    </h3>
                </div>
            </div>
        </div>

        <!-- Content Section -->
        <div class="p-6">
            <p class="mb-4 line-clamp-3 text-sm text-gray-600 dark:text-gray-300">
                {{ theme.description }}
            </p>

            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span
                        class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-300"
                    >
                        {{ theme.courses_count }} {{ theme.courses_count === 1 ? 'course' : 'courses' }}
                    </span>
                </div>

                <div v-if="canEdit || canDelete" class="flex space-x-2">
                    <Link
                        v-if="canEdit"
                        :href="route('themes.edit', theme.id)"
                        @click.stop
                        class="rounded-lg bg-green-50 px-3 py-1 text-sm font-medium text-green-700 transition-colors hover:bg-green-100 dark:bg-green-900/30 dark:text-green-400 dark:hover:bg-green-900/50"
                    >
                        Edit
                    </Link>
                    <button
                        v-if="canDelete"
                        @click.stop="$emit('delete', theme)"
                        class="rounded-lg bg-red-50 px-3 py-1 text-sm font-medium text-red-700 transition-colors hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </Link>
</template>

<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps<{
    theme: {
        id: number;
        name: string;
        description: string;
        courses_count: number;
        cover_image?: string;
    };
}>();

defineEmits<{
    delete: [theme: any];
}>();

const page = usePage();

const canEdit = computed(() => {
    return page.props.auth.user.role === 'admin';
});

const canDelete = computed(() => {
    return page.props.auth.user.role === 'admin';
});
</script>
