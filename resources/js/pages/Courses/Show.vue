<template>
    <Head :title="course.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Hero Section with Cover Image -->
        <div class="relative min-h-[100px] xl:min-h-[200px] overflow-hidden">
            <!-- Background Image -->
            <div
                class="absolute inset-0 bg-cover bg-center bg-no-repeat"
                :style="{ backgroundImage: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' }"
            >
                <!-- Gradient Overlay -->
                <div class="absolute inset-0 bg-gradient-to-r from-black/60 via-black/40 to-black/60 dark:from-black/80 dark:via-black/60 dark:to-black/80"></div>
            </div>
            <div class="flex items-center justify-between absolute right-0 bottom-0 p-12">
                <div class="space-x-3 hidden xl:flex">
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
        <div class="h-1 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 rounded-full z-10 mb-6"></div>

        <!-- Main Content Section -->
        <div class="w-full xl:flex px-12 z-10">
            <!-- Left Side: Course Details -->
            <div class="w-full xl:w-1/2 z-10 px-8 lg:px-12 -mt-16 xl:-mt-32 max-w-3xl xl:max-w-xl">
                <div>
                    <!-- Course Cover Image and title-->
                    <img class="rounded-xl shadow-lg w-full max-h-96 object-cover mb-12" v-if="course.cover_image" :alt="course.title"
                         :src="course.cover_image" />
                    <h1 class="text-xl mb-6 lg:text-3xl font-bold drop-shadow-lg">{{ course.title }}</h1>
                    <p class="text-base mb-6 max-w-3xl text-blue-500 leading-relaxed">{{ course.theme.name }}</p>
                    <p class="text-base mb-6 max-w-3xl leading-relaxed">{{ course.description }}</p>

                    <div class="flex items-center justify-between mb-6">
                        <!-- Coach Information -->
                        <div class="flex items-center space-x-4">
                            <Avatar class="w-16 h-16 ring-2 ring-white/30">
                                <AvatarImage :src="course.coach.avatar" :alt="course.coach.name" />
                                <AvatarFallback class="text-lg font-semibold">
                                    {{ course.coach.name.split(' ').map(n => n[0]).join('').substring(0, 2) }}
                                </AvatarFallback>
                            </Avatar>
                            <div>
                                <p class="text-sm  font-medium">Instructor</p>
                                <p class="text-lg font-semibold">{{ course.coach.name }}</p>
                            </div>
                        </div>
                        <!-- Approval Status (for creators/admins only) -->
                        <div
                            v-if="canEdit"
                            :class="{
                                    'bg-green-500/20 text-green-800 border-green-400/30': course.is_approved,
                                    'bg-yellow-500/20 text-yellow-800 border-yellow-400/30': !course.is_approved,
                                }"
                            class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium border backdrop-blur-sm"
                        >
                        {{ course.is_approved ? 'Approved' : 'Pending Approval' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Modules Section -->
            <div class="w-full xl:w-1/2 z-10 px-8 lg:px-12 max-w-3xl">
                <div class="mt-12 xl:mt-0">
                    <!-- Paywall for restricted course access -->
                    <div v-if="!canAccess" class="mt-6 mb-12">
                        <PaywallCard
                            description="Get lifetime access to this complete course including all modules, video lessons, and downloadable resources."
                            purchasable-type="course"
                            :purchasable-id="course.id"
                            :pricing="pricing"
                            primary-button-class="backdrop-blur-sm bg-green-600/90 hover:bg-green-500/90 text-white h-auto text-base rounded-lg font-semibold border border-green-500/30 transition-all duration-200 shadow-lg hover:shadow-xl"
                            class="backdrop-blur-sm bg-white/10 dark:bg-black/20 border border-white/20 dark:border-white/10 rounded-2xl shadow-xl"
                        />
                    </div>

                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Things you will learn:</h3>

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
                            class="animate-fade-in-up min-w-xl"
                        />
                    </div>
                </div>
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

/* Subtle dot pattern for background texture */
.dot-pattern {
  background-image: radial-gradient(circle at 1px 1px, rgba(74, 85, 104, 0.15) 1px, transparent 0);
  background-size: 20px 20px;
}

.dark .dot-pattern {
  background-image: radial-gradient(circle at 1px 1px, rgba(255, 255, 255, 0.08) 1px, transparent 0);
}
</style>
