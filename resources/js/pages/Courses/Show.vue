<template>
    <Head :title="course.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="mb-4 flex items-center space-x-3">
                        <h1 class="text-2xl font-semibold">{{ course.title }}</h1>
                        <span
                            :class="{
                                'bg-green-100 text-green-800': course.is_approved,
                                'bg-yellow-100 text-yellow-800': !course.is_approved,
                            }"
                            class="rounded px-2 py-1 text-sm font-medium"
                        >
                            {{ course.is_approved ? 'Approved' : 'Pending Approval' }}
                        </span>
                        <!-- Pricing badge -->
                        <span
                            v-if="pricing.formatted_price"
                            :class="{
                                'bg-green-100 text-green-800': pricing.is_free,
                                'bg-blue-100 text-blue-800': !pricing.is_free,
                            }"
                            class="rounded px-2 py-1 text-sm font-medium"
                        >
                            {{ pricing.formatted_price }}
                        </span>
                    </div>
                    <p class="mb-4 text-gray-600">{{ course.description }}</p>
                    <div class="grid grid-cols-1 gap-4 text-sm text-gray-500 md:grid-cols-2">
                        <div><strong>Theme:</strong> {{ course.theme.name }}</div>
                        <div><strong>Coach:</strong> {{ course.coach.name }}</div>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <!-- Purchase Button for non-accessible paid content -->
                    <PurchaseButton
                        v-if="!canAccess && !pricing.is_free"
                        purchasable-type="course"
                        :purchasable-id="course.id"
                        :price="pricing.price"
                        :is-free="pricing.is_free"
                        class="rounded bg-green-600 px-4 py-2 font-bold text-white hover:bg-green-700"
                    />

                    <Link :href="route('courses.index')" class="rounded bg-gray-500 px-4 py-2 font-bold text-white hover:bg-gray-700">
                        Back to Courses
                    </Link>
                    <Link
                        v-if="canEdit"
                        :href="route('courses.edit', course.id)"
                        class="rounded bg-blue-500 px-4 py-2 font-bold text-white hover:bg-blue-700"
                    >
                        Edit Course
                    </Link>
                </div>
            </div>

            <!-- Paywall for restricted course access -->
            <PaywallCard
                v-if="!canAccess"
                description="Get lifetime access to this complete course including all modules, video lessons, and downloadable resources."
                purchasable-type="course"
                :purchasable-id="course.id"
                :pricing="pricing"
                class="mt-6"
            />

            <div v-if="course.modules && course.modules.length > 0" class="mt-8">
                <h3 class="mb-4 text-lg font-semibold">Course Modules</h3>

                <!-- Module Reorder Component for users who can edit -->
                <div v-if="canEdit">
                    <ModuleReorder :course-id="course.id" :modules="course.modules" />
                </div>

                <!-- Read-only module display for users who can't edit -->
                <div v-else class="space-y-3">
                    <ModuleCard
                        v-for="module in course.modules"
                        :key="module.id"
                        :module="module"
                        :has-access="moduleAccess[module.id]"
                        :course-id="course.id"
                        :clickable="true"
                    />
                </div>
            </div>

            <div v-else class="mt-8 py-8 text-center">
                <div class="mb-4 text-gray-400">
                    <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                        />
                    </svg>
                </div>
                <h3 class="mb-2 text-lg font-medium text-gray-900">No modules yet</h3>
                <p class="text-gray-500">This course doesn't have any modules assigned yet.</p>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import ModuleCard from '@/components/ModuleCard.vue';
import ModuleReorder from '@/components/ModuleReorder.vue';
import PaywallCard from '@/components/PaywallCard.vue';
import PurchaseButton from '@/components/PurchaseButton.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

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

interface Module {
    id: string;
    title: string;
    description: string;
    difficulty: 'easy' | 'medium' | 'hard';
    video_url?: string;
    is_free?: boolean;
    pivot: {
        order: number;
    };
}

interface Course {
    id: string;
    title: string;
    description: string;
    is_approved: boolean;
    coach_id: number;
    theme: Theme;
    coach: User;
    modules?: Module[];
}

interface Pricing {
    price?: number;
    is_free: boolean;
    formatted_price: string;
}

const props = defineProps<{
    course: Course;
    canAccess: boolean;
    pricing: Pricing;
    moduleAccess: Record<string, boolean>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Courses', href: '/courses' },
    { title: props.course.title, href: `/courses/${props.course.id}` },
];

const { auth } = usePage().props;

const canEdit = computed(() => {
    return auth.user.role === 'admin' || (auth.user.role === 'coach' && props.course.coach_id === auth.user.id);
});
</script>
