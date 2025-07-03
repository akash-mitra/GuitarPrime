<template>
    <Head :title="module.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex h-full max-w-7xl flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="mb-4 flex items-center space-x-3">
                        <h1 class="text-2xl font-semibold">{{ module.title }}</h1>
                        <span
                            :class="{
                                'bg-green-100 text-green-800': module.difficulty === 'easy',
                                'bg-yellow-100 text-yellow-800': module.difficulty === 'medium',
                                'bg-red-100 text-red-800': module.difficulty === 'hard',
                            }"
                            class="rounded px-2 py-1 text-sm font-medium"
                        >
                            {{ module.difficulty }}
                        </span>
                        <!-- Free module badge -->
                        <span v-if="module.is_free" class="rounded bg-green-100 px-2 py-1 text-sm font-medium text-green-800"> Free Demo </span>
                    </div>
                    <!-- Video Player (admin/coach always have access) -->
                    <div v-if="module.video_url" class="mb-4">
                        <iframe :src="module.video_url" class="h-96 w-full rounded-lg" frameborder="0" allowfullscreen></iframe>
                    </div>
                    <p class="mb-4 text-gray-600">{{ module.description }}</p>
                </div>

                <div class="flex space-x-3">
                    <Link :href="route('modules.index')" class="rounded bg-gray-500 px-4 py-2 font-bold text-white hover:bg-gray-700">
                        Back to Modules
                    </Link>
                    <Link
                        v-if="canEdit"
                        :href="route('modules.edit', module.id)"
                        class="rounded bg-blue-500 px-4 py-2 font-bold text-white hover:bg-blue-700"
                    >
                        Edit Module
                    </Link>
                </div>
            </div>

            <!-- Courses using this module -->
            <div v-if="module.courses && module.courses.length > 0" class="mt-8">
                <h3 class="mb-4 text-lg font-semibold">Used in Courses</h3>
                <div class="grid gap-4 md:grid-cols-2">
                    <div v-for="course in module.courses" :key="course.id" class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <h4 class="font-medium">{{ course.title }}</h4>
                        <p class="mt-1 text-sm text-gray-600">{{ course.description }}</p>
                        <div class="mt-2 text-xs text-gray-500">Order: {{ course.pivot?.order }}</div>
                    </div>
                </div>
            </div>

            <!-- Attachments (admin/coach always have access) -->
            <div v-if="module.attachments && module.attachments.length > 0" class="mt-8">
                <h3 class="mb-4 text-lg font-semibold">Attachments</h3>
                <div class="space-y-2">
                    <div
                        v-for="attachment in module.attachments"
                        :key="attachment.id"
                        class="flex items-center justify-between rounded-lg border bg-gray-50 p-3"
                    >
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        fill-rule="evenodd"
                                        d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ attachment.filename }}</p>
                                <p class="text-xs text-gray-500">{{ (attachment.size / 1024 / 1024).toFixed(2) }} MB</p>
                            </div>
                        </div>
                        <a
                            :href="`/attachments/${attachment.id}/download`"
                            class="inline-flex items-center rounded border border-transparent bg-blue-100 px-3 py-1 text-xs font-medium text-blue-600 hover:bg-blue-200"
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
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Attachment {
    id: string;
    filename: string;
    size: number;
    mime_type: string;
}

interface Course {
    id: string;
    title: string;
    description: string;
    pivot?: {
        order: number;
    };
}

interface Module {
    id: string;
    title: string;
    description: string;
    difficulty: 'easy' | 'medium' | 'hard';
    video_url?: string;
    attachments?: Attachment[];
    courses?: Course[];
}

const props = defineProps<{
    module: Module;
    canAccess: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Modules', href: '/modules' },
    { title: props.module.title, href: `/modules/${props.module.id}` },
];

const { auth } = usePage().props;

const canEdit = computed(() => {
    return auth.user.role === 'admin' || auth.user.role === 'coach';
});
</script>
