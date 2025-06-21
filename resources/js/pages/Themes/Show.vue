<template>
    <Head :title="theme.name" />
    
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold">{{ theme.name }}</h1>
                    <p v-if="theme.description" class="text-gray-600 mt-2">{{ theme.description }}</p>
                </div>
                <div class="flex space-x-2">
                    <Link
                        :href="route('themes.index')"
                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Back to Themes
                    </Link>
                    <Link
                        v-if="$page.props.auth.user.role !== 'student'"
                        :href="route('themes.edit', theme.id)"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Edit Theme
                    </Link>
                </div>
            </div>

            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h2 class="text-xl font-semibold mb-4">Courses in this Theme</h2>
                
                <div v-if="theme.courses.length === 0" class="text-center py-8">
                    <p class="text-gray-500">No approved courses found in this theme.</p>
                    <Link
                        v-if="$page.props.auth.user.role !== 'student'"
                        :href="route('courses.create', { theme_id: theme.id })"
                        class="inline-block mt-4 bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Create First Course
                    </Link>
                </div>

                <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="course in theme.courses"
                        :key="course.id"
                        class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm hover:shadow-md transition-shadow"
                    >
                        <h3 class="font-semibold text-lg mb-2">{{ course.title }}</h3>
                        <p class="text-gray-600 text-sm mb-3 line-clamp-3">
                            {{ course.description }}
                        </p>
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-xs text-gray-500">
                                by {{ course.coach.name }}
                            </span>
                            <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                                Approved
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <Link
                                :href="route('courses.show', course.id)"
                                class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                            >
                                View Course
                            </Link>
                            <div v-if="canManageCourse(course)" class="flex space-x-2">
                                <Link
                                    :href="route('courses.edit', course.id)"
                                    class="text-green-600 hover:text-green-800 text-sm"
                                >
                                    Edit
                                </Link>
                                <button
                                    v-if="$page.props.auth.user.role === 'admin'"
                                    @click="deleteCourse(course)"
                                    class="text-red-600 hover:text-red-800 text-sm"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="$page.props.auth.user.role !== 'student'" class="text-center">
                <Link
                    :href="route('courses.create', { theme_id: theme.id })"
                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                >
                    Add New Course to this Theme
                </Link>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import type { BreadcrumbItem } from '@/types'

const props = defineProps<{
    theme: {
        id: number;
        name: string;
        description?: string;
        courses: Array<{
            id: number;
            title: string;
            description: string;
            coach: {
                id: number;
                name: string;
            };
        }>;
    }
}>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Themes', href: '/themes' },
    { title: props.theme.name, href: route('themes.show', props.theme.id) }
]

const canManageCourse = (course) => {
    const user = usePage().props.auth.user
    return user.role === 'admin' || (user.role === 'coach' && user.id === course.coach.id)
}

const deleteCourse = (course) => {
    if (confirm(`Are you sure you want to delete "${course.title}"?`)) {
        router.delete(route('courses.destroy', course.id))
    }
}
</script>