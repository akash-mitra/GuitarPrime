<template>
    <Head title="Create Course" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <h1 class="text-2xl font-semibold">Create Course</h1>

            <form @submit.prevent="submit">
                <div class="mb-6">
                    <label for="theme_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Theme *
                    </label>
                    <select
                        id="theme_id"
                        v-model="form.theme_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        :class="{ 'border-red-500': form.errors.theme_id }"
                        required
                    >
                        <option value="">Select a theme</option>
                        <option v-for="theme in themes" :key="theme.id" :value="theme.id">
                            {{ theme.name }}
                        </option>
                    </select>
                    <div v-if="form.errors.theme_id" class="text-red-600 text-sm mt-1">
                        {{ form.errors.theme_id }}
                    </div>
                </div>

                <div class="mb-6">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Course Title *
                    </label>
                    <input
                        id="title"
                        v-model="form.title"
                        type="text"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        :class="{ 'border-red-500': form.errors.title }"
                        required
                    />
                    <div v-if="form.errors.title" class="text-red-600 text-sm mt-1">
                        {{ form.errors.title }}
                    </div>
                </div>

                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Course Description *
                    </label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="6"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        :class="{ 'border-red-500': form.errors.description }"
                        required
                    ></textarea>
                    <div v-if="form.errors.description" class="text-red-600 text-sm mt-1">
                        {{ form.errors.description }}
                    </div>
                    <p class="text-sm text-gray-500 mt-1">
                        Describe what students will learn in this course.
                    </p>
                </div>

                <!-- Module Selection - Admin Only -->
                <div v-if="isAdmin && props.modules && props.modules.length > 0" class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Modules (Admin Only)
                    </label>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-3">
                            Select modules to include in this course. Students will see modules in the order you select them.
                        </p>
                        <div class="space-y-2 max-h-60 overflow-y-auto">
                            <div 
                                v-for="module in props.modules" 
                                :key="module.id"
                                class="flex items-start space-x-3 p-2 rounded hover:bg-gray-100"
                            >
                                <input
                                    type="checkbox"
                                    :id="`module-${module.id}`"
                                    :value="module.id"
                                    v-model="form.module_ids"
                                    class="mt-1 h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                />
                                <label :for="`module-${module.id}`" class="flex-1 cursor-pointer">
                                    <div class="flex items-center space-x-2">
                                        <span class="font-medium">{{ module.title }}</span>
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
                                    <p class="text-sm text-gray-600 mt-1">{{ module.description }}</p>
                                </label>
                            </div>
                        </div>
                        <div v-if="form.module_ids.length > 0" class="mt-3 text-sm text-blue-600">
                            {{ form.module_ids.length }} module(s) selected
                        </div>
                    </div>
                    <div v-if="form.errors.module_ids" class="text-red-600 text-sm mt-1">
                        {{ form.errors.module_ids }}
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                Your course will be submitted for admin approval before it becomes visible to students.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <Link
                        :href="route('courses.index')"
                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
                    >
                        <span v-if="form.processing">Creating...</span>
                        <span v-else>Create Course</span>
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import type { BreadcrumbItem } from '@/types'

interface Theme {
    id: string
    name: string
}

interface Module {
    id: string
    title: string
    description: string
    difficulty: 'easy' | 'medium' | 'hard'
    video_url?: string
}

const props = defineProps<{
    themes: Theme[]
    modules?: Module[]
}>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Courses', href: '/courses' },
    { title: 'Create', href: '/courses/create' }
]

const { auth } = usePage().props

const isAdmin = computed(() => {
    return auth.user.role === 'admin'
})

const form = useForm({
    theme_id: '',
    title: '',
    description: '',
    module_ids: [] as string[]
})

const submit = () => {
    form.post(route('courses.store'))
}
</script>
