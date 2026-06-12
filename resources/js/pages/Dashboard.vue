<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ArcElement, Chart, Legend, PieController, Tooltip } from 'chart.js';
import axios from 'axios';
import { nextTick, onBeforeUnmount, onMounted, ref } from 'vue';
import { route } from 'ziggy-js';
import { trans } from '@/helpers/translator';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';

Chart.register(PieController, ArcElement, Tooltip, Legend);

interface DietTypeStat {
    id: number;
    title: string;
    eaters_count: number;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: trans('dashboard'),
        href: dashboard().url,
    },
];

const chartCanvas = ref<HTMLCanvasElement | null>(null);
const chart = ref<Chart<'pie'> | null>(null);
const stats = ref<DietTypeStat[]>([]);
const isLoading = ref(true);
const errorMessage = ref('');

const loadStats = async () => {
    isLoading.value = true;
    errorMessage.value = '';

    try {
        const response = await axios.get<DietTypeStat[]>(route('dashboard.diet_types_stats'));

        const data = response.data;
        stats.value = data;

        await nextTick();

        if (!chartCanvas.value) {
            return;
        }

        chart.value?.destroy();
        chart.value = new Chart(chartCanvas.value, {
            type: 'pie',
            data: {
                labels: data.map((item) => item.title),
                datasets: [
                    {
                        data: data.map((item) => item.eaters_count),
                        backgroundColor: ['#22c55e', '#f59e0b', '#3b82f6', '#ec4899', '#8b5cf6', '#f97316', '#10b981', '#6366f1', '#f43f5e', '#3d4451'],
                        borderColor: '#ffffff',
                        borderWidth: 1,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label(context) {
                                const value = context.parsed;
                                return `${context.label}: ${value} eater${value === 1 ? '' : 's'}`;
                            },
                        },
                    },
                },
            },
        });
    } catch (error) {
        const message = error instanceof Error ? error.message : 'Unexpected error.';
        errorMessage.value = message;
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    void loadStats();
});

onBeforeUnmount(() => {
    chart.value?.destroy();
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                >
                    <div class="mb-3 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-sidebar-foreground">{{ trans('eaters_by_diet_type') }}</p>
                        </div>
                    </div>

                    <div class="h-[80%] w-full">
                        <canvas v-if="stats.length" ref="chartCanvas" class="h-full w-full"></canvas>
                        <div v-else class="flex h-full items-center justify-center rounded-lg border border-dashed border-sidebar-border/80 text-sm text-muted-foreground">
                            <span v-if="isLoading">Loading chart…</span>
                            <span v-else-if="errorMessage">{{ errorMessage }}</span>
                            <span v-else>No diet type data available.</span>
                        </div>
                    </div>
                </div>
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <PlaceholderPattern />
                </div>
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
                >
                    <PlaceholderPattern />
                </div>
            </div>
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border"
            >
                <PlaceholderPattern />
            </div>
        </div>
    </AppLayout>
</template>
