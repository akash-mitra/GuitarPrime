<template>
    <Head :title="module.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
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
                    </div>
                    <p class="text-gray-600 mb-4">{{ module.description }}</p>

                    <div v-if="module.video_url" class="mb-4">
                        <h3 class="text-lg font-medium mb-2">Video</h3>
                        <div class="aspect-video bg-gray-100 rounded-lg flex items-center justify-center">
                            <a
                                :href="module.video_url"
                                target="_blank"
                                class="text-blue-600 hover:text-blue-800"
                            >
                                Watch on Vimeo →
                            </a>
                        </div>
                    </div>
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

            <!-- Attachments -->
            <div v-if="module.attachments && module.attachments.length > 0" class="mt-8">
                <h3 class="text-lg font-semibold mb-4">Attachments</h3>
                <div class="space-y-2">
                    <div
                        v-for="attachment in module.attachments"
                        :key="attachment.id"
                        class="bg-gray-50 rounded-lg p-3 border border-gray-200 flex justify-between items-center"
                    >
                        <div>
                            <h4 class="font-medium text-sm">{{ attachment.filename }}</h4>
                            <p class="text-xs text-gray-500">
                                {{ formatFileSize(attachment.size) }} • {{ attachment.mime_type }}
                            </p>
                        </div>
                        <button class="text-blue-600 hover:text-blue-800 text-sm">
                            Download
                        </button>
                    </div>
                </div>
            </div>

            <div v-else class="mt-8 text-center py-8">
                <div class="text-gray-400 mb-4">
                    <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No attachments</h3>
                <p class="text-gray-500">This module doesn't have any attachments yet.</p>
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

const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 Bytes'
    const k = 1024
    const sizes = ['Bytes', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}
</script>
