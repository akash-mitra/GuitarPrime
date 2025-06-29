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
                        <!-- Pricing badge -->
                        <span
                            v-if="pricing.formatted_price"
                            :class="{
                'bg-green-100 text-green-800': pricing.is_free,
                'bg-blue-100 text-blue-800': !pricing.is_free
              }"
                            class="px-2 py-1 text-sm font-medium rounded"
                        >
              {{ pricing.formatted_price }}
            </span>
                    </div>
                    <p class="text-gray-600 mb-4">{{ module.description }}</p>

                    <!-- Protected Video Player -->
                    <ProtectedVideoPlayer
                        v-if="module.video_url"
                        :video-url="module.video_url"
                        :can-access="canAccess"
                        purchasable-type="module"
                        :purchasable-id="module.id"
                        :pricing="pricing"
                    />
                </div>

                <div class="flex space-x-3">
                    <!-- Purchase Button for non-accessible paid content -->
                    <PurchaseButton
                        v-if="!canAccess && !pricing.is_free"
                        purchasable-type="module"
                        :purchasable-id="module.id"
                        :price="pricing.price"
                        :is-free="pricing.is_free"
                        class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                    />
                    
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

            <!-- Paywall for restricted access -->
            <PaywallCard
                v-if="!canAccess"
                description="Get lifetime access to this module including the video lesson and all downloadable resources."
                purchasable-type="module"
                :purchasable-id="module.id"
                :pricing="pricing"
                class="mt-6"
            />

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

            <!-- Protected Attachments -->
            <ProtectedAttachmentList
                :attachments="module.attachments"
                :can-access="canAccess"
                purchasable-type="module"
                :purchasable-id="module.id"
                :pricing="pricing"
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

interface Pricing {
    price?: number
    is_free: boolean
    formatted_price: string
}

const props = defineProps<{
    module: Module
    canAccess: boolean
    pricing: Pricing
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
