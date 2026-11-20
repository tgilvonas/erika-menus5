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
import { onMounted, onBeforeUnmount, ref } from 'vue';

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
                <template #content><Eater :eater="state.modals.eater.objectInModal" /></template>
            </Modal>
            <Modal modal-name="dietType">
                <template #modal_title>{{ trans('diet') }}</template>
                <template #content><DietType :diet-type="state.modals.dietType.objectInModal" /></template>
            </Modal>
            <Modal modal-name="mealtime">
                <template #modal_title>{{ trans('mealtime') }}</template>
                <template #content><Mealtime :mealtime="state.modals.mealtime.objectInModal" /></template>
            </Modal>
            <Modal modal-name="dish" size="lg">
                <template #modal_title>{{ trans('dish') }}</template>
                <template #content><Dish :dish="state.modals.dish.objectInModal" /></template>
            </Modal>
            <Modal modal-name="ingredient">
                <template #modal_title>{{ trans('ingredient') }}</template>
                <template #content><Ingredient :ingredient="state.modals.ingredient.objectInModal" /></template>
            </Modal>

            <form class="space-y-6" @submit.prevent="saveDay">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium" for="day_date">{{ trans('day') }}</label>
                        <input id="day_date" lang="lt" type="date" v-model="date" required class="w-full rounded border px-3 py-2" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium" for="day_eater">{{ trans('eater') }}</label>
                        <div class="flex gap-2">
                            <select id="day_eater" v-model="eaterId" required class="flex-1 rounded border px-3 py-2">
                                <option value="">{{ trans('select') }}</option>
                                <option v-for="eater in selectedEaters" :key="eater.id" :value="eater.id">{{ eater.name }}</option>
                            </select>
                            <Button type="button" color="green" @click="state.callModal({ modal: 'eater', objectInModal: {} })">+</Button>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <Button type="button" @click="state.callModal({ modal: 'mealtime', objectInModal: {} })">{{ trans('create_mealtime') }}</Button>
                    <Button type="button" @click="state.callModal({ modal: 'dish', objectInModal: {} })">{{ trans('create_dish') }}</Button>
                    <Button type="button" @click="state.callModal({ modal: 'ingredient', objectInModal: {} })">{{ trans('create_ingredient') }}</Button>
                    <Button type="button" @click="state.callModal({ modal: 'dietType', objectInModal: {} })">{{ trans('create_diet') }}</Button>
                </div>

                <div class="flex items-end gap-2">
                    <div class="flex-1">
                        <label class="mb-1 block text-sm font-medium">{{ trans('mealtime') }}</label>
                        <select v-model="selectedMealtimeId" class="w-full rounded border px-3 py-2">
                            <option value="">{{ trans('select') }}</option>
                            <option v-for="mealtime in availableMealtimes" :key="mealtime.id" :value="mealtime.id">{{ mealtimeTitle(mealtime) }}</option>
                        </select>
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
                            <select v-model="selectedDishId" class="w-full rounded border px-3 py-2">
                                <option value="">{{ trans('select') }}</option>
                                <option v-for="dish in dishes" :key="dish.id" :value="dish.id">{{ dishTitle(dish) }}</option>
                            </select>
                        </div>
                        <Button type="button" color="green" @click="addDish(mealtimeIndex)">{{ trans('add_dish') }}</Button>
                    </div>

                    <table v-if="mealtime.dishes.length" class="w-full table-auto border-collapse">
                        <tbody>
                            <tr v-for="(dish, dishIndex) in mealtime.dishes" :key="dish.id">
                                <td class="border p-2">{{ dish.title }}</td>
                                <td class="w-24 border p-2 text-right">
                                    <Button type="button" color="red" @click="removeDish(mealtimeIndex, dishIndex)">{{ trans('delete') }}</Button>
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
