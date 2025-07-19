<template>
    <Head title="Courses" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex h-full max-w-7xl flex-1 flex-col gap-4 rounded-xl p-4 lg:p-6">
            <div class="flex items-center justify-between pb-4">
                <div>
                    <h1 class="text-2xl font-semibold">All Courses</h1>
                    <p class="py-2">
                        Browse our complete catalog of guitar courses covering various styles, techniques, and skill levels.
                    </p>
                </div>
                <div class="flex space-x-3">
                    <Link
                        v-if="auth.user.role === 'admin'"
                        :href="route('courses.approval-queue')"
                        class="rounded bg-yellow-500 px-4 py-2 font-bold text-white hover:bg-yellow-700"
                    >
                        Approval Queue
                    </Link>
                    <Link
                        v-if="auth.user.role === 'admin' || auth.user.role === 'coach'"
                        :href="route('courses.create')"
                        class="rounded bg-blue-500 px-4 py-2 font-bold text-white hover:bg-blue-700"
                    >
                        Create Course
                    </Link>
                </div>
            </div>

            <div class="mb-6">
                <Input
                    v-model="searchQuery"
                    type="text"
                    class="block w-full h-12"
                    placeholder="Search courses by title..."/>
            </div>

            <div v-if="courses.data.length === 0" class="py-8 text-center">
                <p class="text-gray-500">No courses found.</p>
            </div>

            <div v-else class="grid gap-4 md:gap-6 lg:gap-8 xl:gap-10 md:grid-cols-2 lg:grid-cols-3">
                <CourseCard v-for="course in courses.data" :key="course.id" :course="course" @delete="deleteCourse" />
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                <Pagination :links="courses.links" />
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
import { Input } from '@/components/ui/input';
import { Pagination } from '@/components/ui/pagination';

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
