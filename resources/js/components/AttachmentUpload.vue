<template>
    <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">Attachments</label>
        <p class="mb-3 text-sm text-gray-500">Upload files for this module (documents, images, audio, etc.)</p>

        <!-- Upload Area -->
        <div
            @drop.prevent="handleDrop"
            @dragover.prevent
            @dragenter.prevent
            class="relative mb-4 rounded-lg border-2 border-dashed border-gray-300 p-6 text-center hover:border-gray-400 dark:border-gray-700 bg-gray-50 dark:bg-gray-950"
            :class="{ 'border-blue-400 bg-blue-50': isDragOver }"
        >
            <input
                ref="fileInput"
                type="file"
                multiple
                @change="handleFileSelect"
                accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png,.gif,.mp3,.wav,.midi,.mid"
                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
            />
            <div class="pointer-events-none">
                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <p class="mt-2 text-sm text-gray-600">
                    <span class="font-medium text-blue-600">Click to upload</span> or drag and drop
                </p>
                <p class="text-xs text-gray-500">PDF, DOC, TXT, JPG, PNG, MP3, MIDI files</p>
            </div>
        </div>

        <!-- Existing Attachments -->
        <div v-if="existingAttachments.length > 0" class="mb-4">
            <h4 class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-400">Current Attachments</h4>
            <div class="space-y-2">
                <div
                    v-for="attachment in existingAttachments"
                    :key="attachment.id"
                    class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900"
                >
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <input
                                v-model="attachment.name"
                                @blur="updateAttachmentName(attachment)"
                                class="w-full bg-transparent text-sm font-medium text-gray-900 dark:text-gray-200 border-none focus:outline-none focus:ring-0 p-0"
                                placeholder="Enter attachment name"
                            />
                            <p class="text-xs text-gray-500">{{ formatFileSize(attachment.size) }} • {{ attachment.mime_type }}</p>
                        </div>
                    </div>
                    <div class="flex space-x-4">
                        <button
                            @click="downloadAttachment(attachment)"
                            class="text-blue-600 hover:text-blue-800"
                            title="Download"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </button>
                        <button
                            @click="removeAttachment(attachment.id)"
                            class="text-red-600 hover:text-red-800"
                            title="Delete"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Attachments Being Uploaded -->
        <div v-if="newAttachments.length > 0" class="mb-4">
            <h4 class="mb-2 text-sm font-medium text-gray-700">New Attachments</h4>
            <div class="space-y-2">
                <div
                    v-for="(attachment, index) in newAttachments"
                    :key="`new-${index}`"
                    class="flex items-center justify-between rounded-lg border border-blue-200 bg-blue-50 p-3"
                >
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <input
                                v-model="attachment.name"
                                class="w-full bg-transparent text-sm font-medium text-gray-900 border-none focus:outline-none focus:ring-0 p-0"
                                placeholder="Enter attachment name"
                            />
                            <p class="text-xs text-gray-500">{{ formatFileSize(attachment.file.size) }} • {{ attachment.file.type }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div v-if="attachment.uploading" class="text-blue-600">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                        <button
                            @click="removeNewAttachment(index)"
                            class="text-red-600 hover:text-red-800"
                            title="Remove"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

interface Attachment {
    id: string;
    name: string;
    filename: string;
    size: number;
    mime_type: string;
}

interface NewAttachment {
    name: string;
    file: File;
    uploading: boolean;
}

interface Props {
    moduleId?: string;
    attachments?: Attachment[];
}

const props = defineProps<Props>();

const fileInput = ref<HTMLInputElement>();
const isDragOver = ref(false);
const existingAttachments = reactive<Attachment[]>([]);
const newAttachments = reactive<NewAttachment[]>([]);

onMounted(() => {
    if (props.attachments) {
        existingAttachments.push(...props.attachments);
    }
});

const handleDrop = (e: DragEvent) => {
    isDragOver.value = false;
    const files = e.dataTransfer?.files;
    if (files) {
        handleFiles(files);
    }
};

const handleFileSelect = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files) {
        handleFiles(target.files);
    }
};

const handleFiles = (files: FileList) => {
    Array.from(files).forEach(file => {
        const name = file.name.replace(/\.[^/.]+$/, ''); // Remove extension for default name
        newAttachments.push({
            name,
            file,
            uploading: false
        });
    });
};

const removeNewAttachment = (index: number) => {
    newAttachments.splice(index, 1);
};

const removeAttachment = async (attachmentId: string) => {
    if (!confirm('Are you sure you want to delete this attachment?')) {
        return;
    }

    try {
        await router.delete(route('attachments.destroy', attachmentId));
        const index = existingAttachments.findIndex(a => a.id === attachmentId);
        if (index !== -1) {
            existingAttachments.splice(index, 1);
        }
    } catch (error) {
        console.error('Error deleting attachment:', error);
        alert('Failed to delete attachment. Please try again.');
    }
};

const updateAttachmentName = async (attachment: Attachment) => {
    try {
        await router.put(route('attachments.update', attachment.id), {
            name: attachment.name
        });
    } catch (error) {
        console.error('Error updating attachment name:', error);
        alert('Failed to update attachment name. Please try again.');
    }
};

const downloadAttachment = (attachment: Attachment) => {
    window.open(route('attachments.download', attachment.id), '_blank');
};

const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const uploadAttachments = async (targetModuleId?: string) => {
    const moduleId = targetModuleId || props.moduleId;
    if (!moduleId) return;

    const promises = newAttachments.map(async (attachment) => {
        attachment.uploading = true;

        const formData = new FormData();
        formData.append('name', attachment.name);
        formData.append('file', attachment.file);
        formData.append('module_id', moduleId!);

        try {
            const response = await fetch(route('attachments.store'), {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            });

            if (response.ok) {
                const result = await response.json();
                existingAttachments.push(result.attachment);
            } else {
                throw new Error('Upload failed');
            }
        } catch (error) {
            console.error('Upload error:', error);
            alert(`Failed to upload ${attachment.name}. Please try again.`);
        } finally {
            attachment.uploading = false;
        }
    });

    await Promise.all(promises);
    newAttachments.length = 0; // Clear new attachments
};

// Expose upload function to parent
defineExpose({
    uploadAttachments
});
</script>
