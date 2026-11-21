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
        dishes: (mealtime.dishes ?? []).map((dish: any) => normalizeDish(dish)),
    }));
}

function mealtimeTitle(mealtime: any) {
    return mealtime.translation?.translation ?? mealtime.name ?? mealtime.title ?? mealtime.id;
}

function dishTitle(dish: any) {
    return dish.title ?? dish.translation?.translation ?? dish.name ?? dish.id;
}

function mealtimeNutrition(mealtime: any) {
    return mealtime.dishes.reduce((totals: any, dish: any) => {
        totals.proteins += Number(dish.proteins ?? 0);
        totals.fat += Number(dish.fat ?? 0);
        totals.carbohydrates += Number(dish.carbohydrates ?? 0);
        totals.calories += Number(dish.calories ?? 0);
        return totals;
    }, { proteins: 0, fat: 0, carbohydrates: 0, calories: 0 });
}

function formatNutrition(value: number) {
    return Number(value ?? 0).toFixed(3);
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
    mealtime.dishes.push(source ? normalizeDish(source) : {
        id,
        title: String(id),
        proteins: 0,
        fat: 0,
        carbohydrates: 0,
        calories: 0,
    });
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
        syncDishNutrition();
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

function dishNutrition(dish: any) {
    if (dish) {
        const hasTotals = ['proteins_total', 'fat_total', 'carbohydrates_total', 'calories_total'].some((key) => dish[key] !== undefined);
        if (hasTotals) {
            return {
                proteins: Number(dish.proteins_total ?? 0),
                fat: Number(dish.fat_total ?? 0),
                carbohydrates: Number(dish.carbohydrates_total ?? 0),
                calories: Number(dish.calories_total ?? 0),
            };
        }

        return (dish.ingredients ?? []).reduce((totals: any, ingredient: any) => {
            const massNetto = Number(ingredient.pivot?.mass_netto ?? 0);
            totals.proteins += massNetto * Number(ingredient.proteins ?? 0);
            totals.fat += massNetto * Number(ingredient.fat ?? 0);
            totals.carbohydrates += massNetto * Number(ingredient.carbohydrates ?? 0);
            totals.calories += massNetto * Number(ingredient.calories ?? 0);
            return totals;
        }, { proteins: 0, fat: 0, carbohydrates: 0, calories: 0 });
    }

    return { proteins: 0, fat: 0, carbohydrates: 0, calories: 0 };
}

function normalizeDish(dish: any) {
    return {
        id: dish.id,
        title: dishTitle(dish),
        ...dishNutrition(dish),
    };
}

function syncDishNutrition() {
    mealtimes.value.forEach((mealtime) => {
        mealtime.dishes.forEach((dish: any) => {
            const source = dishes.value.find((item) => Number(item.id) === Number(dish.id));
            if (source) {
                Object.assign(dish, dishNutrition(source));
            }
        });
    });
}

const dayNutrition = computed(() => mealtimes.value.reduce((totals: any, mealtime: any) => {
    const nutrition = mealtimeNutrition(mealtime);
    totals.proteins += nutrition.proteins;
    totals.fat += nutrition.fat;
    totals.carbohydrates += nutrition.carbohydrates;
    totals.calories += nutrition.calories;
    return totals;
}, { proteins: 0, fat: 0, carbohydrates: 0, calories: 0 }));

const assignedEater = computed(() => selectedEaters.value.find((eater) =>
    String(eater.id) === String(eaterId.value)
));

const assignedDiet = computed(() => {
    const dietTypeId = assignedEater.value?.diet_type_id;
    if (!dietTypeId) return null;
    return props.dietTypes.find((dietType) => Number(dietType.id) === Number(dietTypeId)) ?? null;
});

function nutritionMatches(minKey: string, maxKey: string, actual: number) {
    if (!assignedDiet.value) return null;
    const min = Number(assignedDiet.value[minKey] ?? 0);
    const max = Number(assignedDiet.value[maxKey] ?? 0);
    return actual >= min && actual <= max;
}

const allNutritionMatches = computed(() => {
    if (!assignedDiet.value) return null;
    return Boolean(
        nutritionMatches('proteins_min', 'proteins_max', dayNutrition.value.proteins)
        && nutritionMatches('fat_min', 'fat_max', dayNutrition.value.fat)
        && nutritionMatches('carbohydrates_min', 'carbohydrates_max', dayNutrition.value.carbohydrates)
        && nutritionMatches('calories_min', 'calories_max', dayNutrition.value.calories)
    );
});

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
                        <thead>
                            <tr>
                                <th class="border p-2 text-left">{{ trans('dish') }}</th>
                                <th class="border p-2 text-right">{{ trans('proteins') }}</th>
                                <th class="border p-2 text-right">{{ trans('fat') }}</th>
                                <th class="border p-2 text-right">{{ trans('carbohydrates') }}</th>
                                <th class="border p-2 text-right">{{ trans('kilocalories') }}</th>
                                <th class="w-24 border p-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(dish, dishIndex) in mealtime.dishes" :key="dish.id">
                                <td class="border p-2">{{ dish.title }}</td>
                                <td class="border p-2 text-right">{{ formatNutrition(dish.proteins) }}</td>
                                <td class="border p-2 text-right">{{ formatNutrition(dish.fat) }}</td>
                                <td class="border p-2 text-right">{{ formatNutrition(dish.carbohydrates) }}</td>
                                <td class="border p-2 text-right">{{ formatNutrition(dish.calories) }}</td>
                                <td class="w-24 border p-2 text-right">
                                    <Button type="button" color="red" @click="removeDish(mealtimeIndex, Number(dishIndex))">{{ trans('delete') }}</Button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="font-semibold">
                                <td class="border p-2">{{ trans('total') }}</td>
                                <td class="border p-2 text-right">{{ formatNutrition(mealtimeNutrition(mealtime).proteins) }}</td>
                                <td class="border p-2 text-right">{{ formatNutrition(mealtimeNutrition(mealtime).fat) }}</td>
                                <td class="border p-2 text-right">{{ formatNutrition(mealtimeNutrition(mealtime).carbohydrates) }}</td>
                                <td class="border p-2 text-right">{{ formatNutrition(mealtimeNutrition(mealtime).calories) }}</td>
                                <td class="border p-2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="rounded border p-4">
                    <div class="mb-3 flex items-center justify-between">
                        <h2 class="text-lg font-semibold">{{ trans('total') }}</h2>
                        <span
                            v-if="allNutritionMatches !== null"
                            class="rounded px-3 py-1 text-sm font-semibold"
                            :class="allNutritionMatches ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                        >
                            {{ allNutritionMatches ? trans('nutrition_matches') : trans('nutrition_does_not_match') }}
                        </span>
                    </div>

                    <table v-if="assignedDiet" class="mb-3 w-full table-auto border-collapse">
                        <thead>
                            <tr>
                                <th class="border p-2 text-left">{{ trans('nutrition') }}</th>
                                <th class="border p-2 text-right">{{ trans('actual') }}</th>
                                <th class="border p-2 text-right">{{ trans('min') }}</th>
                                <th class="border p-2 text-right">{{ trans('max') }}</th>
                                <th class="border p-2 text-center">{{ trans('status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="border p-2">{{ trans('proteins') }}</td>
                                <td class="border p-2 text-right">{{ formatNutrition(dayNutrition.proteins) }}</td>
                                <td class="border p-2 text-right">{{ formatNutrition(assignedDiet.proteins_min) }}</td>
                                <td class="border p-2 text-right">{{ formatNutrition(assignedDiet.proteins_max) }}</td>
                                <td class="border p-2 text-center" :class="nutritionMatches('proteins_min', 'proteins_max', dayNutrition.proteins) ? 'text-green-700' : 'text-red-700'">
                                    {{ nutritionMatches('proteins_min', 'proteins_max', dayNutrition.proteins) ? '✓' : '✕' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="border p-2">{{ trans('fat') }}</td>
                                <td class="border p-2 text-right">{{ formatNutrition(dayNutrition.fat) }}</td>
                                <td class="border p-2 text-right">{{ formatNutrition(assignedDiet.fat_min) }}</td>
                                <td class="border p-2 text-right">{{ formatNutrition(assignedDiet.fat_max) }}</td>
                                <td class="border p-2 text-center" :class="nutritionMatches('fat_min', 'fat_max', dayNutrition.fat) ? 'text-green-700' : 'text-red-700'">
                                    {{ nutritionMatches('fat_min', 'fat_max', dayNutrition.fat) ? '✓' : '✕' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="border p-2">{{ trans('carbohydrates') }}</td>
                                <td class="border p-2 text-right">{{ formatNutrition(dayNutrition.carbohydrates) }}</td>
                                <td class="border p-2 text-right">{{ formatNutrition(assignedDiet.carbohydrates_min) }}</td>
                                <td class="border p-2 text-right">{{ formatNutrition(assignedDiet.carbohydrates_max) }}</td>
                                <td class="border p-2 text-center" :class="nutritionMatches('carbohydrates_min', 'carbohydrates_max', dayNutrition.carbohydrates) ? 'text-green-700' : 'text-red-700'">
                                    {{ nutritionMatches('carbohydrates_min', 'carbohydrates_max', dayNutrition.carbohydrates) ? '✓' : '✕' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="border p-2">{{ trans('kilocalories') }}</td>
                                <td class="border p-2 text-right">{{ formatNutrition(dayNutrition.calories) }}</td>
                                <td class="border p-2 text-right">{{ formatNutrition(assignedDiet.calories_min) }}</td>
                                <td class="border p-2 text-right">{{ formatNutrition(assignedDiet.calories_max) }}</td>
                                <td class="border p-2 text-center" :class="nutritionMatches('calories_min', 'calories_max', dayNutrition.calories) ? 'text-green-700' : 'text-red-700'">
                                    {{ nutritionMatches('calories_min', 'calories_max', dayNutrition.calories) ? '✓' : '✕' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <table v-else class="w-full table-auto border-collapse">
                        <thead>
                            <tr>
                                <th class="border p-2 text-left">{{ trans('proteins') }}</th>
                                <th class="border p-2 text-right">{{ trans('fat') }}</th>
                                <th class="border p-2 text-right">{{ trans('carbohydrates') }}</th>
                                <th class="border p-2 text-right">{{ trans('kilocalories') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="border p-2 text-right">{{ formatNutrition(dayNutrition.proteins) }}</td>
                                <td class="border p-2 text-right">{{ formatNutrition(dayNutrition.fat) }}</td>
                                <td class="border p-2 text-right">{{ formatNutrition(dayNutrition.carbohydrates) }}</td>
                                <td class="border p-2 text-right">{{ formatNutrition(dayNutrition.calories) }}</td>
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
