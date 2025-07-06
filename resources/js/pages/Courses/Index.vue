<template>
    <Head title="Courses" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">All Courses</h1>
                <div class="flex space-x-3">
                    <Link
                        v-if="auth.user.role === 'admin'"
                        :href="route('courses.approval-queue')"
                        class="rounded bg-yellow-500 px-4 py-2 font-bold text-white hover:bg-yellow-700"
                    >
                        Approval Queue
                    </Link>
                    <Link :href="route('courses.create')" class="rounded bg-blue-500 px-4 py-2 font-bold text-white hover:bg-blue-700">
                        Create Course
                    </Link>
                </div>
            </div>

            <div class="mb-4">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search courses by title..."
                    class="w-full max-w-md rounded-lg border border-gray-300 px-4 py-2 focus:border-transparent focus:ring-2 focus:ring-blue-500"
                />
            </div>

            <div v-if="courses.data.length === 0" class="py-8 text-center">
                <p class="text-gray-500">No courses found.</p>
            </div>

            <div v-else class="space-y-4">
                <CourseCard v-for="course in courses.data" :key="course.id" :course="course" @delete="deleteCourse" />
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
    </AppLayout>
</template>

<script setup lang="ts">
import CourseCard from '@/components/CourseCard.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

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
    price?: number | null;
    is_free?: boolean;
    cover_image?: string;
    theme: Theme;
    coach: User;
}

interface PaginatedCourses {
    data: Course[];
    links: any;
    prev_page_url: string | null;
    next_page_url: string | null;
}

const props = defineProps<{
    courses: PaginatedCourses;
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
        route('courses.index'),
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
    { title: 'Courses', href: '/courses' },
];

const { auth } = usePage().props;

const deleteCourse = (course: Course) => {
    if (confirm(`Are you sure you want to delete "${course.title}"?`)) {
        router.delete(route('courses.destroy', course.id));
    }
};
</script>
