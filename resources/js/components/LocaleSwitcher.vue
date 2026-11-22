<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const locale = computed(() => String(page.props.locale ?? 'lt'));

const changeLocale = (event: Event) => {
    const select = event.target as HTMLSelectElement;

    router.post('/locale', { locale: select.value }, {
        preserveScroll: false,
        preserveState: false,
        onSuccess: () => window.location.reload(),
    });
};
</script>

<template>
    <div class="flex items-center">
        <label for="locale" class="sr-only">Language</label>
        <select
            id="locale"
            :value="locale"
            class="h-9 cursor-pointer rounded-md border border-neutral-200 bg-white px-2 text-sm font-medium text-neutral-900 outline-none focus:ring-2 focus:ring-primary dark:border-neutral-700 dark:bg-neutral-900 dark:text-white"
            @change="changeLocale"
        >
            <option value="lt" class="bg-white text-neutral-900 dark:bg-neutral-900 dark:text-white">LT</option>
            <option value="en" class="bg-white text-neutral-900 dark:bg-neutral-900 dark:text-white">EN</option>
        </select>
    </div>
</template>
