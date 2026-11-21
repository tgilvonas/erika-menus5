<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { trans } from '@/helpers/translator';
import Button from '@/components/Button.vue';
import Modal from '@/components/Modal.vue';
import DietType from '@/components/forms/DietType.vue';
import Dish from '@/components/forms/Dish.vue';
import Eater from '@/components/forms/Eater.vue';
import Ingredient from '@/components/forms/Ingredient.vue';
import Mealtime from '@/components/forms/Mealtime.vue';
import state from '@/state.js';
import emitter from '@/eventBus.js';
import axios from 'axios';
import { route } from 'ziggy-js';
import { onMounted, onBeforeUnmount, ref, computed } from 'vue';
import { VueDatePicker } from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import { enUS, lt } from 'date-fns/locale';
import VueSelect from 'vue3-select-component';
import 'vue3-select-component/dist/styles.css';

const props = defineProps<{
    day: any;
    eaters: Array<any>;
    dietTypes: Array<any>;
    mealtimes: Array<any>;
}>();

const eaterId = ref(props.day.eater_id ?? props.day.eater?.id ?? '');
const date = ref(typeof props.day.date === 'string' ? props.day.date : '');
const mealtimes = ref(normalizeMealtimes(props.day.mealtimes));
const selectedEaters = ref([...props.eaters]);
const availableMealtimes = ref([...props.mealtimes]);
const dishes = ref<any[]>([]);
const selectedMealtimeId = ref<number | ''>('');
const selectedDishId = ref<number | ''>('');
const dateModel = ref<Date | null>(parseIsoDate(date.value));
const calendarLocale = computed(() =>
    document.documentElement.lang.toLowerCase().startsWith('lt') ? lt : enUS
);

const breadcrumbs: BreadcrumbItem[] = [
    { title: trans('days'), href: dashboard().url },
];

function normalizeMealtimes(items: any[] = []) {
    return items.map((mealtime) => ({
        id: mealtime.id,
        title: mealtime.translation?.translation ?? mealtime.name ?? mealtime.title ?? '',
        dishes: (mealtime.dishes ?? []).map((dish: any) => ({
            id: dish.id,
            title: dish.translation?.translation ?? dish.name ?? dish.title ?? '',
        })),
    }));
}

function mealtimeTitle(mealtime: any) {
    return mealtime.translation?.translation ?? mealtime.name ?? mealtime.title ?? mealtime.id;
}

function dishTitle(dish: any) {
    return dish.title ?? dish.translation?.translation ?? dish.name ?? dish.id;
}

function addMealtime() {
    if (!selectedMealtimeId.value) return;
    const id = Number(selectedMealtimeId.value);
    if (mealtimes.value.some((item) => item.id === id)) return;

    const source = availableMealtimes.value.find((item) => item.id === id);
    mealtimes.value.push({ id, title: mealtimeTitle(source), dishes: [] });
    selectedMealtimeId.value = '';
}

function removeMealtime(index: number) {
    mealtimes.value.splice(index, 1);
}

function addDish(mealtimeIndex: number) {
    if (!selectedDishId.value) return;
    const id = Number(selectedDishId.value);
    const mealtime = mealtimes.value[mealtimeIndex];
    if (mealtime.dishes.some((dish: any) => dish.id === id)) return;

    const source = dishes.value.find((dish) => Number(dish.id) === id);
    mealtime.dishes.push({ id, title: source ? dishTitle(source) : String(id) });
    selectedDishId.value = '';
}

function removeDish(mealtimeIndex: number, dishIndex: number) {
    mealtimes.value[mealtimeIndex].dishes.splice(dishIndex, 1);
}

function getDishes() {
    axios.get(route('dishes.json_list'), {
        params: { page: 1, search_text: '', paginate_by: 200 },
    }).then((response) => {
        const data = response.data.data ?? response.data;
        dishes.value = data;
    });
}

function getMealtimes() {
    axios.get(route('mealtimes.json_list'), {
        params: { page: 1, search_text: '', paginate_by: 200 },
    }).then((response) => {
        const data = response.data.data ?? response.data;
        availableMealtimes.value = data.map((item: any) => ({
            id: item.id,
            name: item.title,
            title: item.title,
        }));
    });
}

function getEaters() {
    axios.get(route('eaters.json_list'), {
        params: { page: 1, search_text: '', paginate_by: 200 },
    }).then((response) => {
        selectedEaters.value = response.data.data ?? response.data;
    });
}

function handleSaved() {
    getDishes();
    getMealtimes();
    getEaters();
}

function saveDay() {
    router.post(route('days.save'), {
        day_id: props.day.id ?? null,
        date: date.value,
        eater_id: eaterId.value,
        mealtimes: mealtimes.value.map((mealtime) => ({
            id: mealtime.id,
            dishes: mealtime.dishes.map((dish: any) => ({ id: dish.id })),
        })),
    });
}

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

function updateDate(value: Date | null) {
    dateModel.value = value;
    date.value = formatDate(value);
}

const eaterOptions = computed(() => selectedEaters.value.map((eater) => ({
    value: String(eater.id),
    label: eater.name,
})));

const mealtimeOptions = computed(() => availableMealtimes.value.map((mealtime) => ({
    value: Number(mealtime.id),
    label: mealtimeTitle(mealtime),
})));

const dishOptions = computed(() => dishes.value.map((dish) => ({
    value: Number(dish.id),
    label: dishTitle(dish),
})));

function normalizeSelectedValue(value: unknown): number | '' {
    if (Array.isArray(value)) {
        return normalizeSelectedValue(value.length ? value[0] : '');
    }

    if (value && typeof value === 'object') {
        const option = value as { value?: unknown; id?: unknown };
        value = option.value ?? option.id;
    }

    if (value === null || value === undefined || value === '') return '';
    const id = Number(value);
    return Number.isFinite(id) ? id : '';
}

function updateSelectedMealtime(value: unknown) {
    selectedMealtimeId.value = normalizeSelectedValue(value);
}

function updateSelectedDish(value: unknown) {
    selectedDishId.value = normalizeSelectedValue(value);
}

function updateEater(value: unknown) {
    if (Array.isArray(value)) {
        eaterId.value = value.length ? String(value[0]) : '';
    } else {
        eaterId.value = value === null || value === undefined ? '' : String(value);
    }
}

onMounted(() => {
    getDishes();
    getMealtimes();
    emitter.on('dishSaved', handleSaved);
    emitter.on('mealtimeSaved', handleSaved);
    emitter.on('eaterSaved', handleSaved);
    emitter.on('ingredientSaved', getDishes);
});

onBeforeUnmount(() => {
    emitter.off('dishSaved', handleSaved);
    emitter.off('mealtimeSaved', handleSaved);
    emitter.off('eaterSaved', handleSaved);
    emitter.off('ingredientSaved', getDishes);
});
</script>

<template>
    <Head :title="trans('day')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <Modal modal-name="eater">
                <template #modal_title>{{ trans('eater') }}</template>
                <template #content><Eater :eater="state.modals.eater.objectInModal ?? {}" /></template>
            </Modal>
            <Modal modal-name="dietType">
                <template #modal_title>{{ trans('diet') }}</template>
                <template #content><DietType :diet-type="state.modals.dietType.objectInModal ?? {}" /></template>
            </Modal>
            <Modal modal-name="mealtime">
                <template #modal_title>{{ trans('mealtime') }}</template>
                <template #content><Mealtime :mealtime="state.modals.mealtime.objectInModal ?? {}" /></template>
            </Modal>
            <Modal modal-name="dish" size="lg">
                <template #modal_title>{{ trans('dish') }}</template>
                <template #content><Dish :dish="state.modals.dish.objectInModal ?? {}" /></template>
            </Modal>
            <Modal modal-name="ingredient">
                <template #modal_title>{{ trans('ingredient') }}</template>
                <template #content><Ingredient :ingredient="state.modals.ingredient.objectInModal ?? {}" /></template>
            </Modal>

            <form class="space-y-6" @submit.prevent="saveDay">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium" for="day_date">{{ trans('day') }}</label>
                        <VueDatePicker
                            id="day_date"
                            :model-value="dateModel"
                            :locale="calendarLocale"
                            :teleport="true"
                            format="yyyy-MM-dd"
                            auto-apply
                            required
                            :enable-time-picker="false"
                            @update:model-value="updateDate"
                            class="w-full rounded"
                        />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium" for="day_eater">{{ trans('eater') }}</label>
                        <div class="flex gap-2">
                            <VueSelect
                                :options="eaterOptions"
                                :model-value="eaterId ? String(eaterId) : null"
                                :placeholder="trans('select')"
                                :is-clearable="false"
                                input-id="day_eater"
                                @update:model-value="updateEater"
                                class="flex-1"
                            />
                            <Button type="button" color="green" @click="state.callModal({ modal: 'eater', objectInModal: {} })">+</Button>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <Button type="button" @click="state.callModal({ modal: 'mealtime', objectInModal: {} })">{{ trans('create_mealtime') }}</Button>
                    <Button type="button" @click="state.callModal({ modal: 'dish', objectInModal: {} })">{{ trans('create_dish') }}</Button>
                    <Button type="button" @click="state.callModal({ modal: 'dietType', objectInModal: {} })">{{ trans('create_diet') }}</Button>
                </div>

                <div class="flex items-end gap-2">
                    <div class="flex-1">
                        <label class="mb-1 block text-sm font-medium">{{ trans('mealtime') }}</label>
                        <VueSelect
                            :options="mealtimeOptions"
                            :model-value="selectedMealtimeId || null"
                            :placeholder="trans('select')"
                            :is-clearable="true"
                            label="label"
                            value-prop="value"
                            input-id="day-mealtime"
                            @update:model-value="updateSelectedMealtime"
                            class="w-full"
                        />
                    </div>
                    <Button type="button" color="green" @click="addMealtime">{{ trans('add_mealtime') }}</Button>
                </div>

                <div v-for="(mealtime, mealtimeIndex) in mealtimes" :key="mealtime.id" class="rounded border p-4">
                    <div class="mb-3 flex items-center justify-between">
                        <h2 class="text-lg font-semibold">{{ mealtime.title }}</h2>
                        <Button type="button" color="red" @click="removeMealtime(mealtimeIndex)">{{ trans('delete') }}</Button>
                    </div>

                    <div class="mb-3 flex items-end gap-2">
                        <div class="flex-1">
                            <label class="mb-1 block text-sm font-medium">{{ trans('dish') }}</label>
                            <VueSelect
                                :options="dishOptions"
                                :model-value="selectedDishId || null"
                                :placeholder="trans('select')"
                                :is-clearable="true"
                                label="label"
                                value-prop="value"
                                :input-id="`day-dish-${mealtime.id}`"
                                @update:model-value="updateSelectedDish"
                                class="w-full"
                            />
                        </div>
                        <Button type="button" color="green" @click="addDish(mealtimeIndex)">{{ trans('add_dish') }}</Button>
                    </div>

                    <table v-if="mealtime.dishes.length" class="w-full table-auto border-collapse">
                        <tbody>
                            <tr v-for="(dish, dishIndex) in mealtime.dishes" :key="dish.id">
                                <td class="border p-2">{{ dish.title }}</td>
                                <td class="w-24 border p-2 text-right">
                                    <Button type="button" color="red" @click="removeDish(mealtimeIndex, Number(dishIndex))">{{ trans('delete') }}</Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex gap-2 border-t pt-4">
                    <Button type="submit" color="green">{{ trans('save') }}</Button>
                    <Button type="button" color="gray" @click="router.visit(route('days.index'))">{{ trans('back') }}</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
