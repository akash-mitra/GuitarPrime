<template>
    <Head :title="theme.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 lg:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold">{{ theme.name }}</h1>
                    <p v-if="theme.description" class="mt-2 text-gray-600">{{ theme.description }}</p>
                </div>
                <div class="ml-6 flex space-x-2">
                    <Link
                        :href="route('themes.index')"
                        class="rounded bg-gray-500 px-4 py-2 font-bold whitespace-nowrap text-white hover:bg-gray-700"
                    >
                        &laquo; Themes
                    </Link>
                    <Link
                        v-if="$page.props.auth.user.role !== 'student'"
                        :href="route('themes.edit', theme.id)"
                        class="rounded bg-blue-500 px-4 py-2 font-bold text-white hover:bg-blue-700"
                    >
                        Edit Theme
                    </Link>
                </div>
            </div>

            <div class="mb-6 rounded-lg py-4">
                <h2 class="mb-4 text-xl font-semibold">Courses in this theme</h2>

                <div class="mb-6">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search courses in this theme..."
                        class="w-full max-w-md rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-950 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-blue-500"
                    />
                </div>

                <div v-if="courses.data.length === 0" class="py-8 text-center">
                    <p class="text-gray-500">
                        {{ searchQuery ? 'No courses found matching your search.' : 'No approved courses found in this theme.' }}
                    </p>
                    <Link
                        v-if="$page.props.auth.user.role !== 'student' && !searchQuery"
                        :href="route('courses.create', { theme_id: theme.id })"
                        class="mt-4 inline-block rounded bg-green-500 px-4 py-2 font-bold text-white hover:bg-green-700"
                    >
                        Create First Course
                    </Link>
                </div>

                <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <CourseCard v-for="course in courses.data" :key="course.id" :course="transformCourse(course)" @delete="deleteCourse" />
                </div>

                <!-- Pagination -->
                <div v-if="courses.links && courses.links.length > 3" class="mt-6">
                    <nav class="flex items-center justify-center">
                        <div class="flex space-x-1">
                            <template v-for="link in courses.links" :key="link.label">
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

            <div v-if="$page.props.auth.user.role !== 'student'" class="text-center">
                <Link
                    :href="route('courses.create', { theme_id: theme.id })"
                    class="rounded bg-green-500 px-4 py-2 font-bold text-white hover:bg-green-700"
                >
                    Add New Course to this Theme
                </Link>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import CourseCard from '@/components/CourseCard.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps<{
    theme: {
        id: number;
        name: string;
        description?: string;
    };
    courses: {
        data: Array<{
            id: number;
            title: string;
            description: string;
            is_approved: boolean;
            coach_id: number;
            price?: number | null;
            is_free?: boolean;
            cover_image?: string;
            coach: {
                id: number;
                name: string;
            };
            theme: {
                id: string;
                name: string;
            };
        }>;
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
        route('themes.show', props.theme.id),
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
    { title: props.theme.name, href: route('themes.show', props.theme.id) },
];

const transformCourse = (course) => {
    return {
        ...course,
        is_approved: true,
        coach_id: course.coach.id,
        theme: {
            id: props.theme.id.toString(),
            name: props.theme.name,
        },
    };
};

const deleteCourse = (course) => {
    if (confirm(`Are you sure you want to delete "${course.title}"?`)) {
        router.delete(route('courses.destroy', course.id));
    }
};
</script>
