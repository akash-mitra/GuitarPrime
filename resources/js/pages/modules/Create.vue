<template>
    <Head title="Create Module" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <h1 class="text-2xl font-semibold">Create Module</h1>

            <form @submit.prevent="submit">
                <div class="mb-6">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Module Title *
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
                        Module Description *
                    </label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        :class="{ 'border-red-500': form.errors.description }"
                        required
                    ></textarea>
                    <div v-if="form.errors.description" class="text-red-600 text-sm mt-1">
                        {{ form.errors.description }}
                    </div>
                </div>

                <div class="mb-6">
                    <label for="difficulty" class="block text-sm font-medium text-gray-700 mb-2">
                        Difficulty Level *
                    </label>
                    <select
                        id="difficulty"
                        v-model="form.difficulty"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        :class="{ 'border-red-500': form.errors.difficulty }"
                        required
                    >
                        <option value="">Select difficulty</option>
                        <option value="easy">Easy</option>
                        <option value="medium">Medium</option>
                        <option value="hard">Hard</option>
                    </select>
                    <div v-if="form.errors.difficulty" class="text-red-600 text-sm mt-1">
                        {{ form.errors.difficulty }}
                    </div>
                </div>

                <div class="mb-6">
                    <label for="video_url" class="block text-sm font-medium text-gray-700 mb-2">
                        Vimeo Video URL
                    </label>
                    <input
                        id="video_url"
                        v-model="form.video_url"
                        type="url"
                        placeholder="https://vimeo.com/123456789"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        :class="{ 'border-red-500': form.errors.video_url }"
                    />
                    <div v-if="form.errors.video_url" class="text-red-600 text-sm mt-1">
                        {{ form.errors.video_url }}
                    </div>
                    <p class="text-sm text-gray-500 mt-1">
                        Optional. Must be a valid Vimeo URL (e.g., https://vimeo.com/123456789)
                    </p>
                </div>

                <div class="flex items-center justify-between">
                    <Link
                        :href="route('modules.index')"
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
                        <span v-else>Create Module</span>
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import type { BreadcrumbItem } from '@/types'

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Modules', href: '/modules' },
    { title: 'Create', href: '/modules/create' }
]

const form = useForm({
    title: '',
    description: '',
    difficulty: '',
    video_url: ''
})

const submit = () => {
    form.post(route('modules.store'))
}
</script>
