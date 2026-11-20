<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { trans } from '@/helpers/translator';
import Button from '@/components/Button.vue';
import FlashMessage from '@/components/FlashMessage.vue';
import axios from 'axios';
import { route } from 'ziggy-js';
import { ref, onMounted, computed } from 'vue';
import state from '@/state.js';
import { VueDatePicker } from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import { enUS, lt } from 'date-fns/locale';
import VueSelect from 'vue3-select-component';
import 'vue3-select-component/dist/styles.css';

const props = defineProps<{
    days: any;
    eaters: Array<any>;
    filters?: { eater_id?: string; date_from?: string; date_to?: string };
}>();

const eaterId = ref(props.filters?.eater_id ?? '');
const dateFrom = ref(props.filters?.date_from ?? '');
const dateTo = ref(props.filters?.date_to ?? '');

const dateFromModel = ref<Date | null>(parseIsoDate(dateFrom.value));
const dateToModel = ref<Date | null>(parseIsoDate(dateTo.value));
const calendarLocale = computed(() =>
    document.documentElement.lang.toLowerCase().startsWith('lt') ? lt : enUS
);

function parseIsoDate(value: string): Date | null {
    if (!value) return null;
    const [year, month, day] = value.split('-').map(Number);
    return year && month && day ? new Date(year, month - 1, day) : null;
}

function formatDate(value: Date | null): string {
    if (!value) return '';
    const year = value.getFullYear();
    const month = String(value.getMonth() + 1).padStart(2, '0');
    const day = String(value.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function updateDateFrom(value: Date | null) {
    dateFromModel.value = value;
    dateFrom.value = formatDate(value);
    applyFilters();
}

function updateDateTo(value: Date | null) {
    dateToModel.value = value;
    dateTo.value = formatDate(value);
    applyFilters();
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: trans('days'), href: dashboard().url },
];

function applyFilters() {
    router.get(route('days.index'), {
        eater_id: eaterId.value || undefined,
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
    }, { preserveState: true, replace: true });
}

function goToPage(url: string | null) {
    if (url) router.get(url, {}, { preserveState: true });
}

function deleteDay(day: any) {
    if (!confirm(trans('are_you_sure_you_want_to_delete_this_record'))) return;

    axios.post(route('days.delete'), { id: day.id }).then(() => {
        router.reload();
    });
}

function exportPdf(day: any) {
    window.open(route('days.export_pdf', { id: day.id }), '_blank');
}

const eaterOptions = computed(() => props.eaters.map((eater) => ({
    value: String(eater.id),
    label: eater.name,
})));

function updateEaterFilter(value: unknown) {
    if (Array.isArray(value)) {
        eaterId.value = value.length ? String(value[0]) : '';
    } else {
        eaterId.value = value === null || value === undefined ? '' : String(value);
    }
    applyFilters();
}

onMounted(() => {
    const flashMessage = (usePage().props as any).flash?.message;
    if (flashMessage) {
        state.flashSuccessMessage({ message: flashMessage });
    }
});
</script>

<template>
    <Head :title="trans('days')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <FlashMessage type="success" />
            <FlashMessage type="error" />

            <div class="mb-4 flex justify-end">
                <Button color="green" @click="router.visit(route('days.edit', { id: 'new' }))">
                    {{ trans('create_new') }}
                </Button>
            </div>

            <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-4">
                <div>
                    <label class="mb-1 block text-sm font-medium">{{ trans('eater') }}</label>
                    <VueSelect
                        :options="eaterOptions"
                        :model-value="eaterId || null"
                        :placeholder="trans('select')"
                        :is-clearable="true"
                        input-id="eater-filter"
                        @update:model-value="updateEaterFilter"
                        class="w-full rounded border px-3 py-2"
                    />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">{{ trans('date_from') }}</label>
                    <VueDatePicker
                        :model-value="dateFromModel"
                        :locale="calendarLocale"
                        :teleport="true"
                        format="yyyy-MM-dd"
                        auto-apply
                        :enable-time-picker="false"
                        @update:model-value="updateDateFrom"
                        class="w-full rounded border px-3 py-2"
                    />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">{{ trans('date_to') }}</label>
                    <VueDatePicker
                        :model-value="dateToModel"
                        :locale="calendarLocale"
                        :teleport="true"
                        format="yyyy-MM-dd"
                        auto-apply
                        :enable-time-picker="false"
                        @update:model-value="updateDateTo"
                    />
                </div>
            </div>

            <table v-if="props.days.data.length" class="w-full table-auto border-collapse">
                <thead>
                    <tr>
                        <th class="border p-2">{{ trans('day') }}</th>
                        <th class="border p-2">{{ trans('eater') }}</th>
                        <th class="border p-2"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="day in props.days.data" :key="day.id">
                        <td class="border p-2">{{ day.date }}</td>
                        <td class="border p-2">{{ day.eater_name }}</td>
                        <td class="border p-2">
                            <Button @click="router.visit(route('days.edit', { id: day.id }))">{{ trans('edit') }}</Button>
                            <Button color="gray" @click="exportPdf(day)">{{ trans('export_pdf') }}</Button>
                            <Button color="red" @click="deleteDay(day)">{{ trans('delete') }}</Button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div v-else class="py-4">{{ trans('no_records') }}</div>

            <nav v-if="props.days.links.length > 3" class="mt-4 flex gap-2">
                <Button
                    v-for="link in props.days.links"
                    :key="link.label"
                    :disabled="!link.url || link.active"
                    @click="goToPage(link.url)"
                    v-html="link.label"
                />
            </nav>
        </div>
    </AppLayout>
</template>
