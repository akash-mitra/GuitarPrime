<template>
    <Head title="Modules" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Modules</h1>
                <Link :href="route('modules.create')" class="rounded bg-blue-500 px-4 py-2 font-bold text-white hover:bg-blue-700">
                    Create Module
                </Link>
            </div>

            <div class="mb-4">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search modules by title..."
                    class="w-full max-w-md rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-blue-500"
                />
            </div>

            <div v-if="modules.data.length === 0" class="py-8 text-center">
                <p class="text-gray-500">No modules found.</p>
            </div>

            <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div v-for="module in modules.data" :key="module.id" class="rounded-lg border border-gray-200 bg-gray-50 p-6">
                    <div class="mb-3 flex items-start justify-between">
                        <h4 class="text-lg font-semibold">{{ module.title }}</h4>
                        <span
                            :class="{
                                'bg-green-100 text-green-800': module.difficulty === 'easy',
                                'bg-yellow-100 text-yellow-800': module.difficulty === 'medium',
                                'bg-red-100 text-red-800': module.difficulty === 'hard',
                            }"
                            class="rounded px-2 py-1 text-xs font-medium"
                        >
                            {{ module.difficulty }}
                        </span>
                    </div>

                    <p class="mb-3 line-clamp-3 text-sm text-gray-600">
                        {{ module.description }}
                    </p>

                    <div class="mb-3 flex items-center justify-between text-xs text-gray-500">
                        <span>{{ module.attachments_count }} attachments</span>
                        <span v-if="module.video_url" class="text-blue-600">Has Video</span>
                    </div>

                    <div class="flex space-x-2">
                        <Link :href="route('modules.show', module.id)" class="text-sm text-blue-600 hover:text-blue-800"> View </Link>
                        <Link :href="route('modules.edit', module.id)" class="text-sm text-green-600 hover:text-green-800"> Edit </Link>
                        <button v-if="canDelete" @click="deleteModule(module)" class="text-sm text-red-600 hover:text-red-800">Delete</button>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="modules.links && modules.links.length > 3" class="mt-6">
                <nav class="flex items-center justify-center">
                    <div class="flex space-x-1">
                        <template v-for="link in modules.links" :key="link.label">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="border border-gray-300 bg-white px-3 py-2 text-sm leading-tight text-gray-500 hover:bg-gray-100 hover:text-gray-700"
                                :class="{
                                    'border-blue-500 bg-blue-50 text-blue-600': link.active,
                                    'rounded-l-lg': link.label === '&laquo; Previous',
                                    'rounded-r-lg': link.label === 'Next &raquo;',
                                }"
                            >
                                <span v-if="link.label === '&laquo; Previous'">← Previous</span>
                                <span v-else-if="link.label === 'Next &raquo;'">Next →</span>
                                <span v-else>{{ link.label }}</span>
                            </Link>
                            <span
                                v-else
                                class="cursor-default border border-gray-300 bg-white px-3 py-2 text-sm leading-tight text-gray-300"
                                :class="{
                                    'rounded-l-lg': link.label === '&laquo; Previous',
                                    'rounded-r-lg': link.label === 'Next &raquo;',
                                }"
                            >
                                <span v-if="link.label === '&laquo; Previous'">← Previous</span>
                                <span v-else-if="link.label === 'Next &raquo;'">Next →</span>
                                <span v-else>{{ link.label }}</span>
                            </span>
                        </template>
                    </div>
                </nav>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

interface Module {
    id: string;
    title: string;
    description: string;
    difficulty: 'easy' | 'medium' | 'hard';
    video_url?: string;
    attachments_count: number;
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

const canDelete = computed(() => {
    return auth.user.role === 'admin';
});

const deleteModule = (module: Module) => {
    if (confirm(`Are you sure you want to delete "${module.title}"?`)) {
        router.delete(route('modules.destroy', module.id));
    }
};
</script>
