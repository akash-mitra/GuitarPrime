<template>
    <Head :title="course.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-4">
                        <h1 class="text-2xl font-semibold">{{ course.title }}</h1>
                        <span
                            :class="{
                                'bg-green-100 text-green-800': course.is_approved,
                                'bg-yellow-100 text-yellow-800': !course.is_approved
                              }"
                            class="px-2 py-1 text-sm font-medium rounded"
                        >
              {{ course.is_approved ? 'Approved' : 'Pending Approval' }}
            </span>
                    </div>
                    <p class="text-gray-600 mb-4">{{ course.description }}</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-500">
                        <div>
                            <strong>Theme:</strong> {{ course.theme.name }}
                        </div>
                        <div>
                            <strong>Coach:</strong> {{ course.coach.name }}
                        </div>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <Link
                        :href="route('courses.index')"
                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Back to Courses
                    </Link>
                    <Link
                        v-if="canEdit"
                        :href="route('courses.edit', course.id)"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Edit Course
                    </Link>
                </div>
            </div>

            <div v-if="course.modules && course.modules.length > 0" class="mt-8">
                <h3 class="text-lg font-semibold mb-4">Course Modules</h3>
                <div class="space-y-3">
                    <div
                        v-for="module in course.modules"
                        :key="module.id"
                        class="bg-gray-50 rounded-lg p-4 border border-gray-200"
                    >
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-medium">{{ module.title }}</h4>
                                <p class="text-sm text-gray-600 mt-1">{{ module.description }}</p>
                                <span
                                    :class="{
                    'bg-green-100 text-green-800': module.difficulty === 'easy',
                    'bg-yellow-100 text-yellow-800': module.difficulty === 'medium',
                    'bg-red-100 text-red-800': module.difficulty === 'hard'
                  }"
                                    class="inline-block px-2 py-1 text-xs font-medium rounded mt-2"
                                >
                  {{ module.difficulty }}
                </span>
                            </div>
                            <div v-if="module.video_url" class="text-sm text-blue-600">
                                Has Video
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="mt-8 text-center py-8">
                <div class="text-gray-400 mb-4">
                    <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No modules yet</h3>
                <p class="text-gray-500">This course doesn't have any modules assigned yet.</p>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
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

interface Module {
    id: string
    title: string
    description: string
    difficulty: 'easy' | 'medium' | 'hard'
    video_url?: string
}

interface Course {
    id: string
    title: string
    description: string
    is_approved: boolean
    coach_id: number
    theme: Theme
    coach: User
    modules?: Module[]
}

const props = defineProps<{
    course: Course
}>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Courses', href: '/courses' },
    { title: props.course.title, href: `/courses/${props.course.id}` }
]

const { auth } = usePage().props

const canEdit = computed(() => {
    return auth.user.role === 'admin' ||
        (auth.user.role === 'coach' && props.course.coach_id === auth.user.id)
})
</script>
