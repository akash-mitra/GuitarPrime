<template>
    <Head :title="course.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Hero Section with Cover Image -->
        <div class="relative min-h-[450px] overflow-hidden">
            <!-- Background Image -->
            <div
                class="absolute inset-0 bg-cover bg-center bg-no-repeat"
                :style="{ backgroundImage: course.cover_image ? `url('${course.cover_image}')` : 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' }"
            >
                <!-- Gradient Overlay -->
                <div class="absolute inset-0 bg-gradient-to-r from-black/60 via-black/40 to-black/60 dark:from-black/80 dark:via-black/60 dark:to-black/80"></div>
            </div>

            <!-- Glass-morphic Content Overlay -->
            <div class="relative z-10 flex min-h-[400px] items-center px-4 lg:px-6">
                <div class="w-full max-w-4xl mx-auto">
                    <div class="backdrop-blur-md bg-white/10 dark:bg-black/20 rounded-2xl border border-white/20 dark:border-white/10 p-8 shadow-2xl">
                        <!-- Title and Badges -->
                        <div class="mb-6">
                            <div class="flex flex-wrap items-center gap-3 mb-4">
                                <h1 class="text-4xl lg:text-5xl font-bold text-white drop-shadow-lg">{{ course.title }}</h1>

                                <!-- Theme Badge -->
                                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-white/20 dark:bg-white/10 text-white border border-white/30 dark:border-white/20 backdrop-blur-sm">
                                    {{ course.theme.name }}
                                </span>

                                <!-- Free Badge -->
                                <span
                                    v-if="pricing.is_free"
                                    class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-green-500/20 text-green-100 border border-green-400/30 backdrop-blur-sm"
                                >
                                    Free
                                </span>

                                <!-- Approval Status (for creators/admins only) -->
                                <span
                                    v-if="canEdit"
                                    :class="{
                                        'bg-green-500/20 text-green-100 border-green-400/30': course.is_approved,
                                        'bg-yellow-500/20 text-yellow-100 border-yellow-400/30': !course.is_approved,
                                    }"
                                    class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium border backdrop-blur-sm"
                                >
                                    {{ course.is_approved ? 'Approved' : 'Pending Approval' }}
                                </span>
                            </div>

                            <p class="text-lg text-white/90 mb-6 max-w-3xl leading-relaxed">{{ course.description }}</p>
                        </div>

                        <!-- Coach Information -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <Avatar class="w-16 h-16 ring-2 ring-white/30">
                                    <AvatarImage :src="course.coach.avatar" :alt="course.coach.name" />
                                    <AvatarFallback class="bg-white/20 text-white text-lg font-semibold">
                                        {{ course.coach.name.split(' ').map(n => n[0]).join('').substring(0, 2) }}
                                    </AvatarFallback>
                                </Avatar>
                                <div>
                                    <p class="text-sm text-white/70 font-medium">Instructor</p>
                                    <p class="text-xl font-semibold text-white">{{ course.coach.name }}</p>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex space-x-3">
                                <Link
                                    v-if="canEdit"
                                    :href="route('courses.edit', course.id)"
                                    class="backdrop-blur-sm bg-blue-600/90 hover:bg-blue-500/90 text-white px-6 py-3 rounded-lg font-semibold border border-blue-500/30 transition-all duration-200 shadow-lg hover:shadow-xl"
                                >
                                    Edit Course
                                </Link>

                                <Link :href="route('courses.index')" class="backdrop-blur-sm bg-white/10 hover:bg-white/20 text-white px-6 py-3 rounded-lg font-semibold border border-white/30 transition-all duration-200 shadow-lg hover:shadow-xl">
                                    ← All Courses
                                </Link>

                                <!-- Purchase Button for non-accessible paid content -->
                                <PurchaseButton
                                    v-if="!canAccess && !pricing.is_free"
                                    purchasable-type="course"
                                    :purchasable-id="course.id"
                                    :price="pricing.price"
                                    :is-free="pricing.is_free"
                                    class="backdrop-blur-sm bg-green-600/90 hover:bg-green-500/90 text-white px-6 py-3 h-auto text-base rounded-lg font-semibold border border-green-500/30 transition-all duration-200 shadow-lg hover:shadow-xl"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 lg:p-6 max-w-5xl mx-auto">

            <!-- Paywall for restricted course access -->
            <div v-if="!canAccess" class="mt-6">
                <PaywallCard
                    description="Get lifetime access to this complete course including all modules, video lessons, and downloadable resources."
                    purchasable-type="course"
                    :purchasable-id="course.id"
                    :pricing="pricing"
                    class="backdrop-blur-sm bg-white/10 dark:bg-black/20 border border-white/20 dark:border-white/10 rounded-2xl shadow-xl"
                />
            </div>

            <div v-if="course.modules && course.modules.length > 0" class="mt-8">
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Course Modules</h3>

                        <!-- Reorder Toggle Button (only visible to users who can edit) -->
                        <button
                            v-if="canEdit"
                            @click="showReorderMode = !showReorderMode"
                            class="flex items-center space-x-2 rounded-lg backdrop-blur-sm bg-white/10 dark:bg-black/20 border border-white/20 dark:border-white/10 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-lg hover:bg-white/20 dark:hover:bg-black/30 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none transition-all duration-200"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 8V4m0 0h4M4 4l4 4m12-4v4m0-4h-4m4 0l-4 4M4 16v4m0 0h4m-4 0l4-4m12 4l-4-4m4 4v-4m0 4h-4"
                                />
                            </svg>
                            <span>{{ showReorderMode ? 'Exit Reorder Mode' : 'Reorder Modules' }}</span>
                        </button>
                    </div>

                    <div class="h-1 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 rounded-full mb-6"></div>
                </div>

                <!-- Module Reorder Component (shown when reorder mode is active) -->
                <div v-if="canEdit && showReorderMode">
                    <ModuleReorder :course-id="course.id" :modules="course.modules" />
                </div>

                <!-- Normal module display with staggered animation -->
                <div v-else class="space-y-4">
                    <ModuleCard
                        v-for="(module, index) in course.modules"
                        :key="module.id"
                        :module="module"
                        :has-access="moduleAccess[module.id]"
                        :course-id="course.id"
                        :clickable="true"
                        :style="{ animationDelay: `${index * 100}ms` }"
                        class="animate-fade-in-up"
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
import Avatar from '@/components/ui/avatar/Avatar.vue';
import AvatarImage from '@/components/ui/avatar/AvatarImage.vue';
import AvatarFallback from '@/components/ui/avatar/AvatarFallback.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

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

const showReorderMode = ref(false);
</script>

<style scoped>
@keyframes fade-in-up {
  0% {
    opacity: 0;
    transform: translateY(20px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in-up {
  animation: fade-in-up 0.6s ease-out forwards;
  opacity: 0;
}
</style>
