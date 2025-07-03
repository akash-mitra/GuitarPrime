<template>
    <div class="rounded-lg bg-white p-6 shadow">
        <h3 class="mb-4 text-lg font-medium">Reorder Course Modules</h3>

        <div v-if="modules.length === 0" class="py-8 text-center">
            <p class="text-gray-500">No modules assigned to this course yet.</p>
        </div>

        <div v-else>
            <div ref="sortableContainer" class="space-y-3">
                <ModuleCard
                    v-for="(module, index) in sortedModules"
                    :key="module.id"
                    :data-id="module.id"
                    :module="module"
                    :has-access="true"
                    :clickable="false"
                    :draggable="true"
                    :show-access-indicator="false"
                    :show-purchase-button="false"
                    :show-reorder-handle="true"
                    :order-number="index + 1"
                    @dragstart="onDragStart($event, index)"
                    @drop="onDrop($event, index)"
                />
            </div>

            <div class="mt-6 flex items-center justify-between">
                <p class="text-sm text-gray-500">Drag and drop to reorder modules</p>
                <div class="space-x-3">
                    <button @click="resetOrder" class="rounded bg-gray-500 px-4 py-2 text-sm font-bold text-white hover:bg-gray-700">Reset</button>
                    <button
                        @click="saveOrder"
                        :disabled="!hasChanges || saving"
                        class="rounded bg-blue-500 px-4 py-2 text-sm font-bold text-white hover:bg-blue-700 disabled:opacity-50"
                    >
                        <span v-if="saving">Saving...</span>
                        <span v-else>Save Order</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import ModuleCard from '@/components/ModuleCard.vue';
import { router } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';

interface Module {
    id: string;
    title: string;
    description: string;
    difficulty: 'easy' | 'medium' | 'hard';
    pivot: {
        order: number;
    };
}

interface Props {
    courseId: string;
    modules: Module[];
}

const props = defineProps<Props>();

const sortableContainer = ref<HTMLElement>();
const saving = ref(false);
const draggedIndex = ref<number | null>(null);

// Create a reactive copy of modules for reordering
const sortedModules = reactive([...props.modules].sort((a, b) => a.pivot.order - b.pivot.order));

const hasChanges = computed(() => {
    return sortedModules.some((module, index) => {
        const originalModule = props.modules.find((m) => m.id === module.id);
        return originalModule && originalModule.pivot.order !== index + 1;
    });
});

const onDragStart = (event: DragEvent, index: number) => {
    draggedIndex.value = index;
    if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move';
    }
};

const onDrop = (event: DragEvent, dropIndex: number) => {
    event.preventDefault();

    if (draggedIndex.value === null || draggedIndex.value === dropIndex) {
        return;
    }

    // Remove the dragged item and insert it at the new position
    const draggedItem = sortedModules.splice(draggedIndex.value, 1)[0];
    sortedModules.splice(dropIndex, 0, draggedItem);

    draggedIndex.value = null;
};

const resetOrder = () => {
    // Reset to original order
    sortedModules.splice(0, sortedModules.length, ...[...props.modules].sort((a, b) => a.pivot.order - b.pivot.order));
};

const saveOrder = async () => {
    if (!hasChanges.value) return;

    saving.value = true;

    try {
        const modulesWithNewOrder = sortedModules.map((module, index) => ({
            id: module.id,
            order: index + 1,
        }));

        await router.post(
            route('modules.reorder'),
            {
                course_id: props.courseId,
                modules: modulesWithNewOrder,
            },
            {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    // Update the original order values
                    sortedModules.forEach((module, index) => {
                        module.pivot.order = index + 1;
                    });
                },
            },
        );
    } finally {
        saving.value = false;
    }
};
</script>

<style scoped>
.cursor-move {
    cursor: grab;
}

.cursor-move:active {
    cursor: grabbing;
}
</style>
