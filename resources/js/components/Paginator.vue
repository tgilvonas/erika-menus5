<template>
    <nav
        v-if="pagination && pagination.length > 3"
        class="flex items-left justify-left gap-2 mt-6"
    >
        <Button
            v-for="(link, index) in pagination"
            :key="index"
            :disabled="!link.url || link.active"
            @click="emitPage(link)"
            v-html="link.label"
            class="min-w-[40px]"
        />
    </nav>
</template>

<script setup>
import Button from '@/components/Button.vue'

const props = defineProps({
    pagination: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['change'])

function getColor(link) {
    if (link.active) return 'green'
    if (!link.url) return 'red'
    return 'blue'
}

function emitPage(link) {
    if (!link.url || link.active) return

    // Extract ?page= number from URL
    const url = new URL(link.url, window.location.origin)
    const page = Number(url.searchParams.get('page')) || 1
    emit('change', page)
}
</script>
