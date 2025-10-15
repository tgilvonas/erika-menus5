<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { trans } from '@/helpers/translator';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: trans('ingredients'),
        href: dashboard().url,
    },
];
</script>

<template>
    <Head :title="trans('ingredients')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="p-3">
                <p>{{ trans('ingredients') }}</p>
            </div>
        </div>
    </AppLayout>
</template>
