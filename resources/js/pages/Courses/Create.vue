<template>
    <Head title="Create Course" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <h1 class="text-2xl font-semibold">Create Course</h1>

            <form @submit.prevent="submit">
                <div class="mb-6">
                    <label for="theme_id" class="mb-2 block text-sm font-medium text-gray-700"> Theme * </label>
                    <select
                        id="theme_id"
                        v-model="form.theme_id"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        :class="{ 'border-red-500': form.errors.theme_id }"
                        required
                    >
                        <option value="">Select a theme</option>
                        <option v-for="theme in themes" :key="theme.id" :value="theme.id">
                            {{ theme.name }}
                        </option>
                    </select>
                    <div v-if="form.errors.theme_id" class="mt-1 text-sm text-red-600">
                        {{ form.errors.theme_id }}
                    </div>
                </div>

                <div class="mb-6">
                    <label for="title" class="mb-2 block text-sm font-medium text-gray-700"> Course Title * </label>
                    <input
                        id="title"
                        v-model="form.title"
                        type="text"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        :class="{ 'border-red-500': form.errors.title }"
                        required
                    />
                    <div v-if="form.errors.title" class="mt-1 text-sm text-red-600">
                        {{ form.errors.title }}
                    </div>
                </div>

                <div class="mb-6">
                    <label for="description" class="mb-2 block text-sm font-medium text-gray-700"> Course Description * </label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="6"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        :class="{ 'border-red-500': form.errors.description }"
                        required
                    ></textarea>
                    <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">
                        {{ form.errors.description }}
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Describe what students will learn in this course.</p>
                </div>

                <!-- Pricing Section -->
                <div class="mb-6">
                    <div class="mb-3 flex items-center">
                        <input
                            id="is_free"
                            v-model="form.is_free"
                            type="checkbox"
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        />
                        <label for="is_free" class="ml-2 text-sm font-medium text-gray-700"> This course is free </label>
                    </div>

                    <div v-if="!form.is_free" class="mb-4">
                        <label for="price" class="mb-2 block text-sm font-medium text-gray-700"> Price (₹) </label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
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
                                class="w-full rounded-md border border-gray-300 py-2 pr-3 pl-8 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                :class="{ 'border-red-500': form.errors.price }"
                            />
                        </div>
                        <div v-if="form.errors.price" class="mt-1 text-sm text-red-600">
                            {{ form.errors.price }}
                        </div>
                        <p class="mt-1 text-sm text-gray-500">Enter the price in Indian Rupees (₹)</p>
                    </div>
                    <div v-if="form.errors.is_free" class="mt-1 text-sm text-red-600">
                        {{ form.errors.is_free }}
                    </div>
                </div>

                <!-- Module Selection - Admin Only -->
                <div v-if="isAdmin && props.modules && props.modules.length > 0" class="mb-6">
                    <label class="mb-2 block text-sm font-medium text-gray-700"> Modules (Admin Only) </label>
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <p class="mb-3 text-sm text-gray-600">
                            Select modules to include in this course. Students will see modules in the order you select them.
                        </p>
                        <div class="max-h-60 space-y-2 overflow-y-auto">
                            <div v-for="module in props.modules" :key="module.id" class="flex items-start space-x-3 rounded p-2 hover:bg-gray-100">
                                <input
                                    type="checkbox"
                                    :id="`module-${module.id}`"
                                    :value="module.id"
                                    v-model="form.module_ids"
                                    class="mt-1 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                />
                                <label :for="`module-${module.id}`" class="flex-1 cursor-pointer">
                                    <div class="flex items-center space-x-2">
                                        <span class="font-medium">{{ module.title }}</span>
                                        <span
                                            :class="{
                                                'bg-green-100 text-green-800': module.difficulty === 'easy',
                                                'bg-yellow-100 text-yellow-800': module.difficulty === 'medium',
                                                'bg-red-100 text-red-800': module.difficulty === 'hard',
                                            }"
                                            class="rounded px-2 py-1 text-xs font-medium"
                                        >
                                            {{ module.difficulty }}
                                        </span>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-600">{{ module.description }}</p>
                                </label>
                            </div>
                        </div>
                        <div v-if="form.module_ids.length > 0" class="mt-3 text-sm text-blue-600">
                            {{ form.module_ids.length }} module(s) selected
                        </div>
                    </div>
                    <div v-if="form.errors.module_ids" class="mt-1 text-sm text-red-600">
                        {{ form.errors.module_ids }}
                    </div>
                </div>

                <div class="mb-6 rounded-md border border-blue-200 bg-blue-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd"
                                />
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
                    <Link :href="route('courses.index')" class="rounded bg-gray-500 px-4 py-2 font-bold text-white hover:bg-gray-700"> Cancel </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded bg-blue-500 px-4 py-2 font-bold text-white hover:bg-blue-700 disabled:opacity-50"
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
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Theme {
    id: string;
    name: string;
}

interface Module {
    id: string;
    title: string;
    description: string;
    difficulty: 'easy' | 'medium' | 'hard';
    video_url?: string;
}

const props = defineProps<{
    themes: Theme[];
    modules?: Module[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Courses', href: '/courses' },
    { title: 'Create', href: '/courses/create' },
];

const { auth } = usePage().props;

const isAdmin = computed(() => {
    return auth.user.role === 'admin';
});

const form = useForm({
    theme_id: '',
    title: '',
    description: '',
    module_ids: [] as string[],
    price: null as number | null,
    is_free: false,
});

const submit = () => {
    form.post(route('courses.store'));
};
</script>
