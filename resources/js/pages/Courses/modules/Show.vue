<template>
    <Head :title="`${module.title} - ${course.title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 max-w-7xl mx-auto">
            <!-- Course context header -->
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 mb-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">{{ course.title }}</h2>
                        <p class="text-sm text-gray-600">{{ course.theme.name }} • {{ course.coach.name }}</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span
                            :class="{
                                'bg-green-100 text-green-800': coursePricing.is_free,
                                'bg-blue-100 text-blue-800': !coursePricing.is_free
                            }"
                            class="px-2 py-1 text-sm font-medium rounded"
                        >
                            {{ coursePricing.formatted_price }}
                        </span>
                    </div>
                </div>
            </div>

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
                    <!-- Protected Video Player -->
                    <ProtectedVideoPlayer
                        v-if="module.video_url"
                        :video-url="module.video_url"
                        :can-access="canAccessModule"
                        purchasable-type="course"
                        :purchasable-id="course.id"
                        :pricing="coursePricing"
                    />
                    <p class="text-gray-600 mb-4">{{ module.description }}</p>
                </div>

                <div class="flex space-x-3">
                    <!-- Course Purchase Button for non-accessible paid content -->
                    <PurchaseButton
                        v-if="!canAccessCourse && !coursePricing.is_free"
                        purchasable-type="course"
                        :purchasable-id="course.id"
                        :price="coursePricing.price"
                        :is-free="coursePricing.is_free"
                        class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Buy Course
                    </PurchaseButton>

                    <Link
                        :href="route('courses.show', course.id)"
                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Back to Course
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

            <!-- Course-level paywall for restricted access -->
            <PaywallCard
                v-if="!canAccessCourse && !canAccessModule"
                :description="`Get lifetime access to the complete course '${course.title}' including all modules, video lessons, and downloadable resources.`"
                purchasable-type="course"
                :purchasable-id="course.id"
                :pricing="coursePricing"
                class="mt-6"
            />

            <!-- Protected Attachments -->
            <ProtectedAttachmentList
                :attachments="module.attachments"
                :can-access="canAccessModule"
                purchasable-type="course"
                :purchasable-id="course.id"
                :pricing="coursePricing"
            />
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import PaywallCard from '@/components/PaywallCard.vue'
import PurchaseButton from '@/components/PurchaseButton.vue'
import ProtectedVideoPlayer from '@/components/ProtectedVideoPlayer.vue'
import ProtectedAttachmentList from '@/components/ProtectedAttachmentList.vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import type { BreadcrumbItem } from '@/types'

interface Attachment {
    id: string
    filename: string
    size: number
    mime_type: string
}

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

interface Module {
    id: string
    title: string
    description: string
    difficulty: 'easy' | 'medium' | 'hard'
    video_url?: string
    is_free: boolean
    attachments?: Attachment[]
}

interface Pricing {
    price?: number
    is_free: boolean
    formatted_price: string
}

const props = defineProps<{
    course: Course
    module: Module
    canAccessCourse: boolean
    canAccessModule: boolean
    coursePricing: Pricing
}>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Courses', href: '/courses' },
    { title: props.course.title, href: `/courses/${props.course.id}` },
    { title: props.module.title, href: `/courses/${props.course.id}/modules/${props.module.id}` }
]

const { auth } = usePage().props

const canEdit = computed(() => {
    return auth.user.role === 'admin' || 
           (auth.user.role === 'coach' && props.course.coach_id === auth.user.id)
})
</script>