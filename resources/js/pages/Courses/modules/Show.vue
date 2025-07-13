<template>
    <Head :title="`${module.title} - ${course.title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex h-full max-w-7xl flex-1 flex-col gap-4 rounded-xl p-4">
            <!-- Course context header -->
            <div class="mb-4 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/50">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ course.title }}</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ course.theme.name }} • {{ course.coach.name }}</p>
                    </div>
                    <div class="flex items-center space-x-2">
<!--                        <span-->
<!--                            :class="{-->
<!--                                'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300': coursePricing.is_free,-->
<!--                                'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300': !coursePricing.is_free,-->
<!--                            }"-->
<!--                            class="rounded px-2 py-1 text-sm font-medium"-->
<!--                        >-->
<!--                            {{ coursePricing.formatted_price }}-->
<!--                        </span>-->
                        <PurchaseButton
                            v-if="!canAccessCourse && !coursePricing.is_free"
                            purchasable-type="course"
                            :purchasable-id="course.id"
                            :price="coursePricing.price"
                            :is-free="coursePricing.is_free"
                            class="rounded bg-green-600 px-6 py-3 font-bold text-white hover:bg-green-700"
                        >
                            Buy Course
                        </PurchaseButton>
                    </div>
                </div>
            </div>

            <!-- Module header with action buttons -->
            <div class="mb-8 flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                <div class="flex-1">
                    <div class="mb-6 flex flex-wrap items-center gap-4">
                        <h1 class="text-2xl font-semibold">{{ module.title }}</h1>
                        <span
                            :class="{
                                'bg-green-100 text-green-800': module.difficulty === 'easy',
                                'bg-yellow-100 text-yellow-800': module.difficulty === 'medium',
                                'bg-red-100 text-red-800': module.difficulty === 'hard',
                            }"
                            class="rounded px-3 py-2 text-sm font-medium uppercase"
                        >
                            {{ module.difficulty }}
                        </span>
                        <!-- Free module badge -->
                        <span v-if="module.is_free" class="rounded bg-orange-500 px-3 py-2 text-sm font-medium text-orange-100"> Free Demo </span>
                    </div>
                    <p class="mb-6 text-gray-600">{{ module.description }}</p>
                </div>

                <div class="flex flex-wrap gap-4">
                    <!-- Course Purchase Button for non-accessible paid content -->
<!--                    <PurchaseButton-->
<!--                        v-if="!canAccessCourse && !coursePricing.is_free"-->
<!--                        purchasable-type="course"-->
<!--                        :purchasable-id="course.id"-->
<!--                        :price="coursePricing.price"-->
<!--                        :is-free="coursePricing.is_free"-->
<!--                        class="rounded bg-green-600 px-6 py-3 font-bold text-white hover:bg-green-700"-->
<!--                    >-->
<!--                        Buy Course-->
<!--                    </PurchaseButton>-->

<!--                    <Link :href="route('courses.show', course.id)" class="rounded bg-gray-500 px-6 py-3 font-bold text-white hover:bg-gray-700">-->
<!--                        Back to Course-->
<!--                    </Link>-->
                    <Link
                        v-if="canEdit"
                        :href="route('modules.edit', module.id)"
                        class="rounded bg-blue-500 px-6 py-3 font-bold text-white hover:bg-blue-700"
                    >
                        Edit Module
                    </Link>
                </div>
            </div>

            <!-- Two-column layout for xl+ screens, single column for smaller screens -->
            <div class="grid grid-cols-1 gap-8 xl:grid-cols-2">
                <!-- Left column: Video Player -->
                <div class="space-y-8">
                    <!-- Protected Video Player -->
                    <ProtectedVideoPlayer
                        v-if="module.video_url"
                        :video-url="module.video_url"
                        :can-access="canAccessModule"
                        purchasable-type="course"
                        :purchasable-id="course.id"
                        :pricing="coursePricing"
                    />

                    <!-- Course-level paywall for restricted access -->
<!--                    <PaywallCard-->
<!--                        v-if="!canAccessCourse && !canAccessModule"-->
<!--                        :description="`Get lifetime access to the complete course '${course.title}' including all modules, video lessons, and downloadable resources.`"-->
<!--                        purchasable-type="course"-->
<!--                        :purchasable-id="course.id"-->
<!--                        :pricing="coursePricing"-->
<!--                    />-->
                </div>

                <!-- Right column: Attachments -->
                <div class="space-y-8">
                    <!-- Protected Attachments -->
                    <ProtectedAttachmentList
                        :attachments="module.attachments"
                        :can-access="canAccessModule"
                        purchasable-type="course"
                        :purchasable-id="course.id"
                        :pricing="coursePricing"
                    />
                </div>
            </div>

            <!-- Module Navigation -->
            <div class="mt-12 border-t border-gray-200 pt-8 dark:border-gray-700">
                <h3 class="mb-6 text-lg font-medium">Continue Learning</h3>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <!-- Previous Module -->
                    <div v-if="previousModule" class="flex">
                        <Link
                            :href="route('courses.modules.show', { course: course.id, module: previousModule.id })"
                            class="group flex w-full items-center rounded-lg border border-gray-200 bg-gray-50 p-4 transition-colors hover:bg-gray-100 dark:border-gray-800 dark:bg-gray-900/50 dark:hover:bg-gray-800"
                        >
                            <div class="mr-4 flex-shrink-0">
                                <svg class="h-5 w-5 text-gray-400 group-hover:text-gray-600 dark:text-gray-500 dark:group-hover:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Previous</p>
                                <p class="truncate text-sm font-medium text-gray-900 group-hover:text-gray-700 dark:text-gray-100 dark:group-hover:text-gray-200">{{ previousModule.title }}</p>
                            </div>
                        </Link>
                    </div>
                    <div v-else class="hidden md:block"></div>

                    <!-- Next Module -->
                    <div v-if="nextModule" class="flex">
                        <Link
                            :href="route('courses.modules.show', { course: course.id, module: nextModule.id })"
                            class="group flex w-full items-center rounded-lg border border-gray-200 bg-gray-50 p-4 transition-colors hover:bg-gray-100 dark:border-gray-800 dark:bg-gray-900/50 dark:hover:bg-gray-800"
                        >
                            <div class="min-w-0 flex-1 text-right">
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Next</p>
                                <p class="truncate text-sm font-medium text-gray-900 group-hover:text-gray-700 dark:text-gray-100 dark:group-hover:text-gray-200">{{ nextModule.title }}</p>
                            </div>
                            <div class="ml-4 flex-shrink-0">
                                <svg class="h-5 w-5 text-gray-400 group-hover:text-gray-600 dark:text-gray-500 dark:group-hover:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

<script setup lang="ts">
// import PaywallCard from '@/components/PaywallCard.vue';
import ProtectedAttachmentList from '@/components/ProtectedAttachmentList.vue';
import ProtectedVideoPlayer from '@/components/ProtectedVideoPlayer.vue';
import PurchaseButton from '@/components/PurchaseButton.vue';
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
    theme: Theme;
    coach: User;
}

interface Module {
    id: string;
    title: string;
    description: string;
    difficulty: 'easy' | 'medium' | 'hard';
    video_url?: string;
    is_free: boolean;
    attachments?: Attachment[];
}

interface Pricing {
    price?: number;
    is_free: boolean;
    formatted_price: string;
}

const props = defineProps<{
    course: Course;
    module: Module;
    canAccessCourse: boolean;
    canAccessModule: boolean;
    coursePricing: Pricing;
    previousModule?: { id: string; title: string } | null;
    nextModule?: { id: string; title: string } | null;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Courses', href: '/courses' },
    { title: props.course.title, href: `/courses/${props.course.id}` },
    { title: props.module.title, href: `/courses/${props.course.id}/modules/${props.module.id}` },
];

const { auth } = usePage().props;

const canEdit = computed(() => {
    return auth.user.role === 'admin' || (auth.user.role === 'coach' && props.course.coach_id === auth.user.id);
});
</script>
