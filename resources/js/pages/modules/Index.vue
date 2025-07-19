<template>
    <Head title="Modules" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-8">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Modules</h1>
                <Link
                    v-if="auth.user.role === 'admin' || auth.user.role === 'coach'"
                    :href="route('modules.create')"
                    class="rounded bg-blue-500 px-4 py-2 font-bold text-white hover:bg-blue-700"
                >
                    Create Module
                </Link>
            </div>

            <div class="mb-6">
                <Input
                    v-model="searchQuery"
                    type="text"
                    class="block w-full h-12"
                    placeholder="Search modules by title..."/>
            </div>

            <div v-if="modules.data.length === 0" class="py-8 text-center">
                <p class="text-gray-500 dark:text-gray-400">No modules found.</p>
            </div>

            <div v-else class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                <div v-for="module in modules.data" :key="module.id" class="rounded-lg border border-gray-200 bg-gray-50 p-6 dark:border-gray-700 dark:bg-gray-800">
                    <div class="mb-3 flex items-start justify-between">
                        <h4 class="text-lg font-semibold dark:text-white">{{ module.title }}</h4>
                        <span
                            :class="{
                                'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200': module.difficulty === 'easy',
                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-200': module.difficulty === 'medium',
                                'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200': module.difficulty === 'hard',
                            }"
                            class="rounded px-2 py-1 text-xs font-medium"
                        >
                            {{ module.difficulty }}
                        </span>
                    </div>

                    <p class="mb-3 line-clamp-3 text-sm text-gray-600 dark:text-gray-300">
                        {{ module.description }}
                    </p>

                    <div class="mb-3 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span>{{ module.attachments_count }} attachments</span>
                        <span v-if="module.video_url" class="text-blue-600 dark:text-blue-400">Has Video</span>
                    </div>

                    <div class="flex space-x-2">
                        <Link :href="route('modules.show', module.id)" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"> View </Link>
                        <Link v-if="module.can_edit" :href="route('modules.edit', module.id)" class="text-sm text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300">
                            Edit
                        </Link>
                        <button v-if="module.can_delete" @click="deleteModule(module)" class="text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">Delete</button>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                <Pagination :links="modules.links" />
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Input } from '@/components/ui/input';
import { Pagination } from '@/components/ui/pagination';

interface Module {
    id: string;
    title: string;
    description: string;
    difficulty: 'easy' | 'medium' | 'hard';
    video_url?: string;
    attachments_count: number;
    can_edit: boolean;
    can_delete: boolean;
}

interface PaginatedModules {
    data: Module[];
    links: any;
    prev_page_url: string | null;
    next_page_url: string | null;
}

const props = defineProps<{
    modules: PaginatedModules;
    filters: {
        search?: string;
    };
}>();

const searchQuery = ref(props.filters.search || '');

const debounce = <T extends (...args: any[]) => any>(func: T, delay: number) => {
    let timeoutId: number;
    return (...args: Parameters<T>) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => func(...args), delay);
    };
};

const debouncedSearch = debounce((query: string) => {
    router.get(
        route('modules.index'),
        { search: query || undefined },
        {
            preserveState: true,
            replace: true,
        },
    );
}, 300);

watch(searchQuery, (newQuery) => {
    debouncedSearch(newQuery);
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Modules', href: '/modules' },
];

const { auth } = usePage().props;

const deleteModule = (module: Module) => {
    if (confirm(`Are you sure you want to delete "${module.title}"?`)) {
        router.delete(route('modules.destroy', module.id));
    }
};
</script>
