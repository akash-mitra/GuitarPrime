<template>
    <Head :title="module.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 max-w-7xl mx-auto">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-4">
                        <h1 class="text-2xl font-semibold">{{ module.title }}</h1>
                        <span
                            :class="{
                                'bg-green-100 text-green-800': module.difficulty === 'easy',
                                'bg-yellow-100 text-yellow-800': module.difficulty === 'medium',
                                'bg-red-100 text-red-800': module.difficulty === 'hard'
                              }"
                            class="px-2 py-1 text-sm font-medium rounded"
                        >
                            {{ module.difficulty }}
                        </span>
                        <!-- Free module badge -->
                        <span
                            v-if="module.is_free"
                            class="bg-green-100 text-green-800 px-2 py-1 text-sm font-medium rounded"
                        >
                            Free Demo
                        </span>
                    </div>
                    <!-- Video Player (admin/coach always have access) -->
                    <div v-if="module.video_url" class="mb-4">
                        <iframe
                            :src="module.video_url"
                            class="w-full h-96 rounded-lg"
                            frameborder="0"
                            allowfullscreen
                        ></iframe>
                    </div>
                    <p class="text-gray-600 mb-4">{{ module.description }}</p>
                </div>

                <div class="flex space-x-3">

                    <Link
                        :href="route('modules.index')"
                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Back to Modules
                    </Link>
                    <Link
                        v-if="canEdit"
                        :href="route('modules.edit', module.id)"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Edit Module
                    </Link>
                </div>
            </div>


            <!-- Courses using this module -->
            <div v-if="module.courses && module.courses.length > 0" class="mt-8">
                <h3 class="text-lg font-semibold mb-4">Used in Courses</h3>
                <div class="grid gap-4 md:grid-cols-2">
                    <div
                        v-for="course in module.courses"
                        :key="course.id"
                        class="bg-gray-50 rounded-lg p-4 border border-gray-200"
                    >
                        <h4 class="font-medium">{{ course.title }}</h4>
                        <p class="text-sm text-gray-600 mt-1">{{ course.description }}</p>
                        <div class="text-xs text-gray-500 mt-2">
                            Order: {{ course.pivot?.order }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attachments (admin/coach always have access) -->
            <div v-if="module.attachments && module.attachments.length > 0" class="mt-8">
                <h3 class="text-lg font-semibold mb-4">Attachments</h3>
                <div class="space-y-2">
                    <div
                        v-for="attachment in module.attachments"
                        :key="attachment.id"
                        class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border"
                    >
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ attachment.filename }}</p>
                                <p class="text-xs text-gray-500">{{ (attachment.size / 1024 / 1024).toFixed(2) }} MB</p>
                            </div>
                        </div>
                        <a
                            :href="`/attachments/${attachment.id}/download`"
                            class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded text-blue-600 bg-blue-100 hover:bg-blue-200"
                        >
                            Download
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import type { BreadcrumbItem } from '@/types'

interface Attachment {
    id: string
    filename: string
    size: number
    mime_type: string
}

interface Course {
    id: string
    title: string
    description: string
    pivot?: {
        order: number
    }
}

interface Module {
    id: string
    title: string
    description: string
    difficulty: 'easy' | 'medium' | 'hard'
    video_url?: string
    attachments?: Attachment[]
    courses?: Course[]
}


const props = defineProps<{
    module: Module
    canAccess: boolean
}>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Modules', href: '/modules' },
    { title: props.module.title, href: `/modules/${props.module.id}` }
]

const { auth } = usePage().props

const canEdit = computed(() => {
    return auth.user.role === 'admin' || auth.user.role === 'coach'
})
</script>
