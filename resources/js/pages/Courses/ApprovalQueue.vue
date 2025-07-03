<template>
    <Head title="Course Approval Queue" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Course Approval Queue</h1>
                <Link :href="route('courses.index')" class="rounded bg-gray-500 px-4 py-2 font-bold text-white hover:bg-gray-700">
                    Back to Courses
                </Link>
            </div>

            <div v-if="courses.data.length === 0" class="py-8 text-center">
                <div class="mx-auto h-12 w-12 text-gray-400">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="mt-4 text-sm font-medium text-gray-900">No pending approvals</h3>
                <p class="mt-1 text-sm text-gray-500">All courses have been reviewed.</p>
            </div>

            <div v-else class="space-y-4">
                <div v-for="course in courses.data" :key="course.id" class="rounded-lg border border-gray-200 bg-gray-50 p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="mb-2 flex items-center space-x-3">
                                <h4 class="text-lg font-semibold">{{ course.title }}</h4>
                                <span class="rounded bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-800"> Pending Approval </span>
                            </div>
                            <p class="mb-3 text-sm text-gray-600">{{ course.description }}</p>
                            <div class="space-y-1 text-xs text-gray-500">
                                <p>Theme: {{ course.theme.name }}</p>
                                <p>Coach: {{ course.coach.name }} ({{ course.coach.email }})</p>
                                <p>Submitted: {{ formatDate(course.created_at) }}</p>
                            </div>
                        </div>
                        <div class="ml-4 flex space-x-3">
                            <Link
                                :href="route('courses.show', course.id)"
                                class="rounded bg-blue-500 px-4 py-2 text-sm font-bold text-white hover:bg-blue-700"
                            >
                                Preview
                            </Link>
                            <button
                                @click="approveCourse(course)"
                                class="rounded bg-green-500 px-4 py-2 text-sm font-bold text-white hover:bg-green-700"
                            >
                                Approve
                            </button>
                            <button @click="deleteCourse(course)" class="rounded bg-red-500 px-4 py-2 text-sm font-bold text-white hover:bg-red-700">
                                Reject
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="courses.links" class="mt-6">
                <nav class="flex items-center justify-between">
                    <div class="flex flex-1 justify-between sm:hidden">
                        <Link
                            v-if="courses.prev_page_url"
                            :href="courses.prev_page_url"
                            class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-400"
                        >
                            Previous
                        </Link>
                        <Link
                            v-if="courses.next_page_url"
                            :href="courses.next_page_url"
                            class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-400"
                        >
                            Next
                        </Link>
                    </div>
                </nav>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';

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
    created_at: string;
    theme: Theme;
    coach: User;
}

interface PaginatedCourses {
    data: Course[];
    links: any;
    prev_page_url: string | null;
    next_page_url: string | null;
}

defineProps<{
    courses: PaginatedCourses;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Courses', href: '/courses' },
    { title: 'Approval Queue', href: '/courses/approval-queue' },
];

const approveCourse = (course: Course) => {
    if (confirm(`Approve "${course.title}" by ${course.coach.name}?`)) {
        router.post(route('courses.approve', course.id));
    }
};

const deleteCourse = (course: Course) => {
    if (confirm(`Reject and delete "${course.title}"? This action cannot be undone.`)) {
        router.delete(route('courses.destroy', course.id));
    }
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>
