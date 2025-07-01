<template>
    <Head title="Edit Course" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <h1 class="text-2xl font-semibold">Edit Course</h1>

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
                </div>

                <!-- Pricing Section -->
                <div class="mb-6">
                    <div class="flex items-center mb-3">
                        <input
                            id="is_free"
                            v-model="form.is_free"
                            type="checkbox"
                            class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        />
                        <label for="is_free" class="ml-2 text-sm font-medium text-gray-700">
                            This course is free
                        </label>
                    </div>
                    
                    <div v-if="!form.is_free" class="mb-4">
                        <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
                            Price (₹)
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">₹</span>
                            </div>
                            <input
                                id="price"
                                v-model="form.price"
                                type="number"
                                step="0.01"
                                min="0"
                                max="999999"
                                placeholder="0.00"
                                class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :class="{ 'border-red-500': form.errors.price }"
                            />
                        </div>
                        <div v-if="form.errors.price" class="text-red-600 text-sm mt-1">
                            {{ form.errors.price }}
                        </div>
                        <p class="text-sm text-gray-500 mt-1">
                            Enter the price in Indian Rupees (₹)
                        </p>
                    </div>
                    <div v-if="form.errors.is_free" class="text-red-600 text-sm mt-1">
                        {{ form.errors.is_free }}
                    </div>
                </div>

                <!-- Module Management - Admin Only -->
                <div v-if="isAdmin && props.modules && props.modules.length > 0" class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Modules (Admin Only)
                    </label>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-3">
                            Select modules to include in this course. Use the reorder functionality on the course details page to change the order.
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
                                        <span 
                                            v-if="currentModuleIds.includes(module.id)"
                                            class="bg-blue-100 text-blue-800 px-2 py-1 text-xs font-medium rounded"
                                        >
                                            Currently Assigned
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

                <div v-if="!course.is_approved" class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                This course is pending approval. Changes will need to be re-approved by an admin.
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
                        <span v-if="form.processing">Updating...</span>
                        <span v-else>Update Course</span>
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
    pivot?: {
        order: number
    }
}

interface Course {
    id: string
    theme_id: string
    title: string
    description: string
    is_approved: boolean
    price?: number | null
    is_free: boolean
    modules?: Module[]
}

const props = defineProps<{
    course: Course
    themes: Theme[]
    modules?: Module[]
}>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Courses', href: '/courses' },
    { title: 'Edit', href: `/courses/${props.course.id}/edit` }
]

const { auth } = usePage().props

const isAdmin = computed(() => {
    return auth.user.role === 'admin'
})

// Get currently assigned module IDs
const currentModuleIds = computed(() => {
    return props.course.modules?.map(m => m.id) || []
})

const form = useForm({
    theme_id: props.course.theme_id,
    title: props.course.title,
    description: props.course.description,
    module_ids: currentModuleIds.value,
    price: props.course.price ? props.course.price / 100 : null, // Convert paisa to rupees for display
    is_free: props.course.is_free
})

const submit = () => {
    form.put(route('courses.update', props.course.id))
}
</script>
