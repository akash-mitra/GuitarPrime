<template>
    <div class="rounded-lg border border-gray-200 bg-gray-50 p-6 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex gap-4">
            <div class="flex-shrink-0">
                <img v-if="theme.cover_image" :src="theme.cover_image" :alt="theme.name" class="h-16 w-16 rounded-lg object-cover" />
                <div v-else class="flex h-16 w-16 items-center justify-center rounded-lg bg-gray-200 dark:bg-gray-600">
                    <span class="text-xs text-gray-400 dark:text-gray-300">No Image</span>
                </div>
            </div>
            <div class="min-w-0 flex-1">
                <Link
                    :href="route('themes.show', theme.id)"
                    class="mb-2 block text-lg font-semibold text-gray-900 transition-colors hover:text-blue-600 dark:text-white dark:hover:text-blue-400"
                >
                    {{ theme.name }}
                </Link>
                <p class="mb-3 line-clamp-3 text-sm text-gray-600 dark:text-gray-300">
                    {{ theme.description }}
                </p>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-500 dark:text-gray-400"> {{ theme.courses_count }} courses </span>
                    <div v-if="canEdit || canDelete" class="flex space-x-2">
                        <Link
                            v-if="canEdit"
                            :href="route('themes.edit', theme.id)"
                            class="text-sm text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300"
                        >
                            Edit
                        </Link>
                        <button
                            v-if="canDelete"
                            @click="$emit('delete', theme)"
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
