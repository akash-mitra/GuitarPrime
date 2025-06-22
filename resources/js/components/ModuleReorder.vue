<template>
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium mb-4">Reorder Course Modules</h3>

        <div v-if="modules.length === 0" class="text-center py-8">
            <p class="text-gray-500">No modules assigned to this course yet.</p>
        </div>

        <div v-else>
            <div
                ref="sortableContainer"
                class="space-y-3"
            >
                <div
                    v-for="(module, index) in sortedModules"
                    :key="module.id"
                    :data-id="module.id"
                    class="bg-gray-50 rounded-lg p-4 border border-gray-200 cursor-move hover:bg-gray-100 transition-colors"
                    draggable="true"
                    @dragstart="onDragStart($event, index)"
                    @dragover.prevent
                    @drop="onDrop($event, index)"
                >
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M7 2a1 1 0 000 2h6a1 1 0 100-2H7zM4 5a2 2 0 012-2h8a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 2a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h.01a1 1 0 100-2H10zm3 0a1 1 0 000 2h.01a1 1 0 100-2H13zm-6 3a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h.01a1 1 0 100-2H10zm3 0a1 1 0 000 2h.01a1 1 0 100-2H13z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center space-x-2">
                                <span class="text-sm font-medium text-gray-500">#{{ index + 1 }}</span>
                                <h4 class="font-medium">{{ module.title }}</h4>
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
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-between items-center">
                <p class="text-sm text-gray-500">
                    Drag and drop to reorder modules
                </p>
                <div class="space-x-3">
                    <button
                        @click="resetOrder"
                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm"
                    >
                        Reset
                    </button>
                    <button
                        @click="saveOrder"
                        :disabled="!hasChanges || saving"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm disabled:opacity-50"
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
import { router } from '@inertiajs/vue3'
import { computed, ref, reactive } from 'vue'

interface Module {
    id: string
    title: string
    description: string
    difficulty: 'easy' | 'medium' | 'hard'
    pivot: {
        order: number
    }
}

interface Props {
    courseId: string
    modules: Module[]
}

const props = defineProps<Props>()

const sortableContainer = ref<HTMLElement>()
const saving = ref(false)
const draggedIndex = ref<number | null>(null)

// Create a reactive copy of modules for reordering
const sortedModules = reactive([...props.modules].sort((a, b) => a.pivot.order - b.pivot.order))

const hasChanges = computed(() => {
    return sortedModules.some((module, index) => {
        const originalModule = props.modules.find(m => m.id === module.id)
        return originalModule && originalModule.pivot.order !== (index + 1)
    })
})

const onDragStart = (event: DragEvent, index: number) => {
    draggedIndex.value = index
    if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move'
    }
}

const onDrop = (event: DragEvent, dropIndex: number) => {
    event.preventDefault()

    if (draggedIndex.value === null || draggedIndex.value === dropIndex) {
        return
    }

    // Remove the dragged item and insert it at the new position
    const draggedItem = sortedModules.splice(draggedIndex.value, 1)[0]
    sortedModules.splice(dropIndex, 0, draggedItem)

    draggedIndex.value = null
}

const resetOrder = () => {
    // Reset to original order
    sortedModules.splice(0, sortedModules.length,
        ...[...props.modules].sort((a, b) => a.pivot.order - b.pivot.order)
    )
}

const saveOrder = async () => {
    if (!hasChanges.value) return

    saving.value = true

    try {
        const modulesWithNewOrder = sortedModules.map((module, index) => ({
            id: module.id,
            order: index + 1
        }))

        await router.post(route('modules.reorder'), {
            course_id: props.courseId,
            modules: modulesWithNewOrder
        }, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                // Update the original order values
                sortedModules.forEach((module, index) => {
                    module.pivot.order = index + 1
                })
            }
        })
    } finally {
        saving.value = false
    }
}
</script>

<style scoped>
.cursor-move {
    cursor: grab;
}

.cursor-move:active {
    cursor: grabbing;
}
</style>
