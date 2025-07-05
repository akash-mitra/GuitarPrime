<template>
    <div class="mb-4">
        <h3 class="mb-2 text-lg font-medium">Video</h3>

        <!-- Show video if user has access -->
        <div v-if="canAccess" class="aspect-video rounded-lg bg-gray-100">
            <!-- YouTube embedded player -->
            <div v-if="isYouTubeUrl" class="h-full w-full">
                <iframe
                    :src="youTubeEmbedUrl"
                    class="h-full w-full rounded-lg"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    allowfullscreen
                    title="YouTube video player"
                ></iframe>
            </div>
            
            <!-- Vimeo or other video links -->
            <div v-else class="flex h-full items-center justify-center">
                <a :href="videoUrl" target="_blank" class="flex items-center space-x-2 text-blue-600 hover:text-blue-800">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
                        />
                    </svg>
                    <span>{{ isVimeoUrl ? 'Watch on Vimeo' : 'Watch Video' }} →</span>
                </a>
            </div>
        </div>

        <!-- Show paywall if user doesn't have access -->
        <div v-else class="relative aspect-video overflow-hidden rounded-lg bg-gray-100">
            <!-- Blurred background -->
            <div class="absolute inset-0 bg-gradient-to-r from-gray-300 to-gray-400 opacity-30"></div>

            <!-- Video icon overlay -->
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="text-center">
                    <svg class="mx-auto mb-4 h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"
                        />
                    </svg>

                    <div class="mb-4">
                        <h4 class="mb-1 text-lg font-semibold text-gray-700">Premium Video Content</h4>
                        <p class="text-sm text-gray-600">Purchase to unlock this video lesson</p>
                    </div>

                    <PurchaseButton
                        :purchasable-type="purchasableType"
                        :purchasable-id="purchasableId"
                        :price="pricing.price"
                        :is-free="pricing.is_free"
                        class="border border-gray-300 bg-white text-gray-900 hover:bg-gray-50"
                    />
                </div>
            </div>

            <!-- Lock icon in corner -->
            <div class="absolute top-4 right-4">
                <svg class="h-8 w-8 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                    />
                </svg>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import PurchaseButton from '@/components/PurchaseButton.vue';

interface Props {
    videoUrl?: string;
    canAccess: boolean;
    purchasableType: 'course' | 'module';
    purchasableId: string;
    pricing: {
        price?: number;
        is_free: boolean;
        formatted_price: string;
    };
}

const props = defineProps<Props>();

// Helper function to extract YouTube video ID from URL
const extractYouTubeId = (url: string): string | null => {
    if (!url) return null;
    
    const patterns = [
        /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\n?#]+)/,
        /(?:youtube\.com\/watch\?.*v=)([^&\n?#]+)/
    ];
    
    for (const pattern of patterns) {
        const match = url.match(pattern);
        if (match && match[1]) {
            return match[1];
        }
    }
    
    return null;
};

// Check if URL is from YouTube
const isYouTubeUrl = computed(() => {
    if (!props.videoUrl) return false;
    return /(?:youtube\.com|youtu\.be)/.test(props.videoUrl);
});

// Check if URL is from Vimeo
const isVimeoUrl = computed(() => {
    if (!props.videoUrl) return false;
    return /vimeo\.com/.test(props.videoUrl);
});

// Generate YouTube embed URL
const youTubeEmbedUrl = computed(() => {
    if (!props.videoUrl || !isYouTubeUrl.value) return '';
    
    const videoId = extractYouTubeId(props.videoUrl);
    if (!videoId) return '';
    
    return `https://www.youtube.com/embed/${videoId}`;
});
</script>
