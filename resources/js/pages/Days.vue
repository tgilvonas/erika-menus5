<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import {trans} from "@/helpers/translator";

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: trans('days'),
        href: dashboard().url,
    },
];
</script>

<template>
    <Head :title="trans('days')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <div>To be developed here</div>
        </div>
    </AppLayout>
</template>
