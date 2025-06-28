<template>
    <Head title="Themes" />
    
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-semibold">Themes</h1>
                            <Link
                                v-if="$page.props.auth.user.role !== 'student'"
                                :href="route('themes.create')"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                            >
                                Create Theme
                            </Link>
            </div>

            <div v-if="themes.data.length === 0" class="text-center py-8">
                <p class="text-gray-500">No themes found.</p>
            </div>

            <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <ThemeCard
                    v-for="theme in themes.data"
                    :key="theme.id"
                    :theme="theme"
                    @delete="deleteTheme"
                />
            </div>

            <div v-if="themes.links" class="mt-6">
                <nav class="flex items-center justify-between">
                    <div class="flex justify-between flex-1 sm:hidden">
                        <Link
                            v-if="themes.prev_page_url"
                            :href="themes.prev_page_url"
                            class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:text-gray-400"
                        >
                            Previous
                        </Link>
                        <Link
                            v-if="themes.next_page_url"
                            :href="themes.next_page_url"
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
import ThemeCard from '@/components/ThemeCard.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import type { BreadcrumbItem } from '@/types'

defineProps<{
    themes: {
        data: Array<any>;
        links?: any;
        prev_page_url?: string;
        next_page_url?: string;
    }
}>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Themes', href: '/themes' }
]

const deleteTheme = (theme: any) => {
    if (confirm(`Are you sure you want to delete "${theme.name}"?`)) {
        router.delete(route('themes.destroy', theme.id))
    }
}
</script>
