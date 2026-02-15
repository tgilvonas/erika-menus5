<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import {trans} from "@/helpers/translator";

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: trans('dishes'),
        href: dashboard().url,
    },
];
</script>

<template>
    <Head :title="trans('dishes')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            Dishes
        </div>
    </AppLayout>
</template>
