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
                <CourseCard
                    v-for="course in courses.data"
                    :key="course.id"
                    :course="course"
                    @delete="deleteCourse"
                />
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
import CourseCard from '@/components/CourseCard.vue'
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


const deleteCourse = (course: Course) => {
    if (confirm(`Are you sure you want to delete "${course.title}"?`)) {
        router.delete(route('courses.destroy', course.id))
    }
}
</script>
