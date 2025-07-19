<template>
    <nav v-if="links && links.length > 3" class="flex items-center justify-center">
        <div class="flex space-x-1">
            <template v-for="link in links" :key="link.label">
                <Button
                    v-if="link.url"
                    :as="Link"
                    :href="link.url"
                    :variant="link.active ? 'default' : 'outline'"
                    size="sm"
                    :class="{
                        'rounded-l-lg': link.label.indexOf('Previous') >= 0,
                        'rounded-r-lg': link.label.indexOf('Next') >= 0,
                    }"
                >
                    <span v-if="link.label.indexOf('Previous') >= 0">← Previous</span>
                    <span v-else-if="link.label.indexOf('Next') >= 0">Next →</span>
                    <span v-else>{{ link.label }}</span>
                </Button>
                <Button
                    v-else
                    variant="outline"
                    size="sm"
                    disabled
                    :class="{
                        'rounded-l-lg': link.label.indexOf('Previous') >= 0,
                        'rounded-r-lg': link.label.indexOf('Next') >= 0,
                    }"
                >
                    <span v-if="link.label.indexOf('Previous') >= 0">← Previous</span>
                    <span v-else-if="link.label.indexOf('Next') >= 0">Next →</span>
                    <span v-else>{{ link.label }}</span>
                </Button>
            </template>
        </div>
    </nav>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Link } from '@inertiajs/vue3'

interface PaginationLink {
    url: string | null
    label: string
    active: boolean
}

defineProps<{
    links: PaginationLink[]
}>()
</script>
