<template>
    <Head title="Create Module" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-8 max-w-6xl">
            <h1 class="text-2xl font-semibold">Create Module</h1>

            <form @submit.prevent="submit">
                <div class="mb-6">
                    <label for="title" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-500"> Module Title * </label>
                    <input
                        id="title"
                        v-model="form.title"
                        type="text"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none bg-gray-50 dark:bg-gray-950 dark:border-gray-700"
                        :class="{ 'border-red-500': form.errors.title }"
                        required
                    />
                    <div v-if="form.errors.title" class="mt-1 text-sm text-red-600">
                        {{ form.errors.title }}
                    </div>
                </div>

                <div class="mb-6">
                    <label for="description" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-500"> Module Description * </label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="4"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none bg-gray-50 dark:bg-gray-950 dark:border-gray-700"
                        :class="{ 'border-red-500': form.errors.description }"
                        required
                    ></textarea>
                    <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">
                        {{ form.errors.description }}
                    </div>
                </div>

                <div class="mb-6">
                    <label for="difficulty" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-500"> Difficulty Level * </label>
                    <select
                        id="difficulty"
                        v-model="form.difficulty"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none bg-gray-50 dark:bg-gray-950 dark:border-gray-700"
                        :class="{ 'border-red-500': form.errors.difficulty }"
                        required
                    >
                        <option value="">Select difficulty</option>
                        <option value="easy">Easy</option>
                        <option value="medium">Medium</option>
                        <option value="hard">Hard</option>
                    </select>
                    <div v-if="form.errors.difficulty" class="mt-1 text-sm text-red-600">
                        {{ form.errors.difficulty }}
                    </div>
                </div>

                <div class="mb-6">
                    <label for="video_url" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-500"> Vimeo Video URL </label>
                    <input
                        id="video_url"
                        v-model="form.video_url"
                        type="url"
                        placeholder="https://vimeo.com/123456789"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none bg-gray-50 dark:bg-gray-950 dark:border-gray-700"
                        :class="{ 'border-red-500': form.errors.video_url }"
                    />
                    <div v-if="form.errors.video_url" class="mt-1 text-sm text-red-600">
                        {{ form.errors.video_url }}
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Optional. Must be a valid Vimeo URL (e.g., https://vimeo.com/123456789)</p>
                </div>

                <!-- Pricing Section -->
                <div class="mb-6 rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                    <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">Pricing</h3>
                    
                    <div class="mb-4">
                        <label class="flex items-center">
                            <input
                                type="checkbox"
                                v-model="form.is_free"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900"
                            />
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">This module is free</span>
                        </label>
                    </div>

                    <div v-if="!form.is_free" class="mb-4">
                        <label for="price" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-500">
                            Price (₹)
                        </label>
                        <input
                            id="price"
                            v-model="form.price"
                            type="number"
                            step="0.01"
                            min="0"
                            max="999999"
                            placeholder="99.00"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none bg-gray-50 dark:bg-gray-950 dark:border-gray-700"
                            :class="{ 'border-red-500': form.errors.price }"
                        />
                        <div v-if="form.errors.price" class="mt-1 text-sm text-red-600">
                            {{ form.errors.price }}
                        </div>
                        <p class="mt-1 text-sm text-gray-500">Enter the price in rupees. Leave blank if module is free.</p>
                    </div>
                </div>

                <div class="mb-6">
                    <AttachmentUpload ref="attachmentUpload" />
                </div>

                <div class="flex items-center justify-between">
                    <Link :href="route('modules.index')" class="rounded bg-gray-500 px-4 py-2 font-bold text-white hover:bg-gray-700"> Cancel </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded bg-blue-500 px-4 py-2 font-bold text-white hover:bg-blue-700 disabled:opacity-50"
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
import AppLayout from '@/layouts/AppLayout.vue';
import AttachmentUpload from '@/components/AttachmentUpload.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Modules', href: '/modules' },
    { title: 'Create', href: '/modules/create' },
];

const attachmentUpload = ref<InstanceType<typeof AttachmentUpload>>();

const form = useForm({
    title: '',
    description: '',
    difficulty: '',
    video_url: '',
    price: null as number | null,
    is_free: false,
});

const submit = async () => {
    try {
        // Create the module first and get the response
        const response = await fetch(route('modules.store'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({
                title: form.title,
                description: form.description,
                difficulty: form.difficulty,
                video_url: form.video_url,
                price: form.price,
                is_free: form.is_free,
            })
        });

        if (!response.ok) {
            const errorData = await response.json();
            // Handle validation errors
            if (response.status === 422) {
                form.setError(errorData.errors || {});
                return;
            }
            throw new Error('Module creation failed');
        }

        const result = await response.json();
        const moduleId = result.moduleId;

        // Upload attachments if any and if we have a module ID
        if (attachmentUpload.value && moduleId) {
            await attachmentUpload.value.uploadAttachments(moduleId);
        }

        // Redirect to modules index
        window.location.href = route('modules.index');
    } catch (error) {
        console.error('Error during module creation:', error);
        alert('Failed to create module. Please try again.');
    }
};
</script>
