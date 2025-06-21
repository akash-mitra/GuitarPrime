<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Course</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
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
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/layouts/AuthLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    course: Object,
    themes: Array
})

const form = useForm({
    theme_id: props.course.theme_id,
    title: props.course.title,
    description: props.course.description
})

const submit = () => {
    form.put(route('courses.update', props.course.id))
}
</script>
