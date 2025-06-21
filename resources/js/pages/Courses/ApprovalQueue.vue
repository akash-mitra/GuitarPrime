<template>
    <Head title="Course Approval Queue" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-semibold">Course Approval Queue</h1>
                <Link
                    :href="route('courses.index')"
                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                >
                    Back to Courses
                </Link>
            </div>

            <div v-if="courses.data.length === 0" class="text-center py-8">
                <div class="mx-auto h-12 w-12 text-gray-400">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="mt-4 text-sm font-medium text-gray-900">No pending approvals</h3>
                <p class="mt-1 text-sm text-gray-500">All courses have been reviewed.</p>
            </div>

            <div v-else class="space-y-4">
                <div
                    v-for="course in courses.data"
                    :key="course.id"
                    class="bg-gray-50 rounded-lg p-6 border border-gray-200"
                >
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3 mb-2">
                                <h4 class="font-semibold text-lg">{{ course.title }}</h4>
                                <span class="bg-yellow-100 text-yellow-800 px-2 py-1 text-xs font-medium rounded">
                  Pending Approval
                </span>
                            </div>
                            <p class="text-gray-600 text-sm mb-3">{{ course.description }}</p>
                            <div class="text-xs text-gray-500 space-y-1">
                                <p>Theme: {{ course.theme.name }}</p>
                                <p>Coach: {{ course.coach.name }} ({{ course.coach.email }})</p>
                                <p>Submitted: {{ formatDate(course.created_at) }}</p>
                            </div>
                        </div>
                        <div class="flex space-x-3 ml-4">
                            <Link
                                :href="route('courses.show', course.id)"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm"
                            >
                                Preview
                            </Link>
                            <button
                                @click="approveCourse(course)"
                                class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm"
                            >
                                Approve
                            </button>
                            <button
                                @click="deleteCourse(course)"
                                class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-sm"
                            >
                                Reject
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="courses.links" class="mt-6">
                <nav class="flex items-center justify-between">
                    <div class="flex justify-between flex-1 sm:hidden">
                        <Link
                            v-if="courses.prev_page_url"
                            :href="courses.prev_page_url"
                            class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:text-gray-400"
                        >
                            Previous
                        </Link>
                        <Link
                            v-if="courses.next_page_url"
                            :href="courses.next_page_url"
                            class="relative ml-3 inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:text-gray-400"
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
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import type { BreadcrumbItem } from '@/types'

interface User {
    id: number
    name: string
    email: string
    role: string
}

interface Theme {
    id: string
    name: string
}

interface Course {
    id: string
    title: string
    description: string
    is_approved: boolean
    created_at: string
    theme: Theme
    coach: User
}

interface PaginatedCourses {
    data: Course[]
    links: any
    prev_page_url: string | null
    next_page_url: string | null
}

defineProps<{
    courses: PaginatedCourses
}>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Courses', href: '/courses' },
    { title: 'Approval Queue', href: '/courses/approval-queue' }
]

const approveCourse = (course: Course) => {
    if (confirm(`Approve "${course.title}" by ${course.coach.name}?`)) {
        router.post(route('courses.approve', course.id))
    }
}

const deleteCourse = (course: Course) => {
    if (confirm(`Reject and delete "${course.title}"? This action cannot be undone.`)) {
        router.delete(route('courses.destroy', course.id))
    }
}

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}
</script>
