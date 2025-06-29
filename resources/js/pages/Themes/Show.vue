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

                <div v-else class="space-y-4">
                    <CourseCard
                        v-for="course in theme.courses"
                        :key="course.id"
                        :course="transformCourse(course)"
                        @delete="deleteCourse"
                    />
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
import CourseCard from '@/components/CourseCard.vue'
import { Head, Link, router } from '@inertiajs/vue3'
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
            is_approved: boolean;
            coach_id: number;
            coach: {
                id: number;
                name: string;
            };
            theme: {
                id: string;
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

const transformCourse = (course) => {
    return {
        ...course,
        is_approved: true,
        coach_id: course.coach.id,
        theme: {
            id: props.theme.id.toString(),
            name: props.theme.name
        }
    }
}

const deleteCourse = (course) => {
    if (confirm(`Are you sure you want to delete "${course.title}"?`)) {
        router.delete(route('courses.destroy', course.id))
    }
}
</script>