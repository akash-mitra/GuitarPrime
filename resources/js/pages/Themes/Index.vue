<template>
    <Head title="Themes" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex h-full max-w-7xl flex-1 flex-col gap-4 rounded-xl p-4 lg:p-6">
            <div class="flex items-center justify-between pb-4">
                <div>
                    <h1 class="text-2xl font-semibold">Themes</h1>
                    <p class="py-2">
                        Our Guitar course catalog is arranged around specific styles or themes to help you focus on niche learning goals.
                    </p>
                </div>

                <Link
                    v-if="$page.props.auth.user && $page.props.auth.user.role === 'admin'"
                    :href="route('themes.create')"
                    class="rounded bg-blue-500 px-4 py-2 font-bold text-white hover:bg-blue-700"
                >
                    Create Theme
                </Link>
            </div>

            <div class="mb-6">
                <Input
                    v-model="searchQuery"
                    type="text"
                    class="block w-full h-12"
                    placeholder="Search themes by name..."/>
            </div>

            <div v-if="themes.data.length === 0" class="py-8 text-center">
                <p class="text-gray-500">No themes found.</p>
            </div>

            <div v-else class="grid gap-4 md:gap-6 lg:gap-8 xl:gap-10 md:grid-cols-2 lg:grid-cols-3">
                <ThemeCard v-for="theme in themes.data" :key="theme.id" :theme="theme" @delete="deleteTheme" />
            </div>

            <!-- Pagination -->
            <div v-if="themes.links && themes.links.length > 3" class="mt-6">
                <nav class="flex items-center justify-center">
                    <div class="flex space-x-1">
                        <template v-for="link in themes.links" :key="link.label">
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
import ThemeCard from '@/components/ThemeCard.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Input } from '@/components/ui/input';

const props = defineProps<{
    themes: {
        data: Array<any>;
        links?: any;
        prev_page_url?: string;
        next_page_url?: string;
    };
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
        route('themes.index'),
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
    { title: 'Themes', href: '/themes' },
];

const deleteTheme = (theme: any) => {
    if (confirm(`Are you sure you want to delete "${theme.name}"?`)) {
        router.delete(route('themes.destroy', theme.id));
    }
};
</script>
