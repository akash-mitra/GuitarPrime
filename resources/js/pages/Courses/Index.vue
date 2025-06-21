<template>
    <Head title="Courses" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-semibold">My Courses</h1>
                <div class="flex space-x-3">
                    <Link
                        v-if="auth.user.role === 'admin'"
                        :href="route('courses.approval-queue')"
                        class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Approval Queue
                    </Link>
                    <Link
                        :href="route('courses.create')"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Create Course
                    </Link>
                </div>
            </div>

            <div v-if="courses.data.length === 0" class="text-center py-8">
                <p class="text-gray-500">No courses found.</p>
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
                                <span
                                    :class="{
                    'bg-green-100 text-green-800': course.is_approved,
                    'bg-yellow-100 text-yellow-800': !course.is_approved
                  }"
                                    class="px-2 py-1 text-xs font-medium rounded"
                                >
                  {{ course.is_approved ? 'Approved' : 'Pending' }}
                </span>
                            </div>
                            <p class="text-gray-600 text-sm mb-2">{{ course.description }}</p>
                            <div class="text-xs text-gray-500 space-y-1">
                                <p>Theme: {{ course.theme.name }}</p>
                                <p>Coach: {{ course.coach.name }}</p>
                            </div>
                        </div>
                        <div class="flex space-x-2 ml-4">
                            <Link
                                :href="route('courses.show', course.id)"
                                class="text-blue-600 hover:text-blue-800 text-sm"
                            >
                                View
                            </Link>
                            <Link
                                v-if="canEdit(course)"
                                :href="route('courses.edit', course.id)"
                                class="text-green-600 hover:text-green-800 text-sm"
                            >
                                Edit
                            </Link>
                            <button
                                v-if="canDelete(course)"
                                @click="deleteCourse(course)"
                                class="text-red-600 hover:text-red-800 text-sm"
                            >
                                Delete
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
import { Head, Link, router, usePage } from '@inertiajs/vue3'
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
    coach_id: number
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
    { title: 'Courses', href: '/courses' }
]

const { auth } = usePage().props

const canEdit = (course: Course) => {
    return auth.user.role === 'admin' || (auth.user.role === 'coach' && course.coach_id === auth.user.id)
}

const canDelete = (course: Course) => {
    return auth.user.role === 'admin'
}

const deleteCourse = (course: Course) => {
    if (confirm(`Are you sure you want to delete "${course.title}"?`)) {
        router.delete(route('courses.destroy', course.id))
    }
}
</script>
