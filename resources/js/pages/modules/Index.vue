<template>
    <Head title="Modules" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-semibold">Modules</h1>
                <Link
                    :href="route('modules.create')"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                >
                    Create Module
                </Link>
            </div>

            <div v-if="modules.data.length === 0" class="text-center py-8">
                <p class="text-gray-500">No modules found.</p>
            </div>

            <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="module in modules.data"
                    :key="module.id"
                    class="bg-gray-50 rounded-lg p-6 border border-gray-200"
                >
                    <div class="flex justify-between items-start mb-3">
                        <h4 class="font-semibold text-lg">{{ module.title }}</h4>
                        <span
                            :class="{
                'bg-green-100 text-green-800': module.difficulty === 'easy',
                'bg-yellow-100 text-yellow-800': module.difficulty === 'medium',
                'bg-red-100 text-red-800': module.difficulty === 'hard'
              }"
                            class="px-2 py-1 text-xs font-medium rounded"
                        >
              {{ module.difficulty }}
            </span>
                    </div>

                    <p class="text-gray-600 text-sm mb-3 line-clamp-3">
                        {{ module.description }}
                    </p>

                    <div class="flex justify-between items-center text-xs text-gray-500 mb-3">
                        <span>{{ module.attachments_count }} attachments</span>
                        <span v-if="module.video_url" class="text-blue-600">Has Video</span>
                    </div>

                    <div class="flex space-x-2">
                        <Link
                            :href="route('modules.show', module.id)"
                            class="text-blue-600 hover:text-blue-800 text-sm"
                        >
                            View
                        </Link>
                        <Link
                            :href="route('modules.edit', module.id)"
                            class="text-green-600 hover:text-green-800 text-sm"
                        >
                            Edit
                        </Link>
                        <button
                            v-if="canDelete"
                            @click="deleteModule(module)"
                            class="text-red-600 hover:text-red-800 text-sm"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="modules.links" class="mt-6">
                <nav class="flex items-center justify-between">
                    <div class="flex justify-between flex-1 sm:hidden">
                        <Link
                            v-if="modules.prev_page_url"
                            :href="modules.prev_page_url"
                            class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:text-gray-400"
                        >
                            Previous
                        </Link>
                        <Link
                            v-if="modules.next_page_url"
                            :href="modules.next_page_url"
                            class="relative ml-3 inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:text-gray-400"
                        >
                            Next
                        </Link>
                    </div>
                </nav>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import type { BreadcrumbItem } from '@/types'

interface Module {
    id: string
    title: string
    description: string
    difficulty: 'easy' | 'medium' | 'hard'
    video_url?: string
    attachments_count: number
}

interface PaginatedModules {
    data: Module[]
    links: any
    prev_page_url: string | null
    next_page_url: string | null
}

defineProps<{
    modules: PaginatedModules
}>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Modules', href: '/modules' }
]

const { auth } = usePage().props

const canDelete = computed(() => {
    return auth.user.role === 'admin'
})

const deleteModule = (module: Module) => {
    if (confirm(`Are you sure you want to delete "${module.title}"?`)) {
        router.delete(route('modules.destroy', module.id))
    }
}
</script>
