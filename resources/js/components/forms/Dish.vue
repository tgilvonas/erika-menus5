<script setup>
import Button from '@/components/Button.vue';
import { Input } from '@/components/ui/input';
import { trans } from '@/helpers/translator';
import state from '@/state.js';
import axios from "axios";
import { route } from 'ziggy-js';
import emitter from '@/eventBus.js';
import {reactive, ref, onMounted, computed} from "vue";
import draggable from 'vuedraggable';
import VueSelect from "vue3-select-component";
import "vue3-select-component/styles";

const props = defineProps({
    dish: {
        type: Object,
        default: null
    }
});

const dish = reactive({
    title: '',
    ingredients: [],
});
const formIsValid = ref(true);

const selectedIngredientId = ref(null);
const ingredientsForSelect = ref([]);
const ingredientsMap = reactive({});

let errors = initErrorsObject();

onMounted(() => {
    getIngredients();
});

function initErrorsObject() {
    return {
        title: '',
        ingredients: [],
    }
}

function saveDish() {

    validate();
    if (formIsValid.value === false) {
        return;
    }

    axios.post(route('dishes.save'), {
        id: dish.id ?? null,
        dish_title: dish.title,
        ingredients: dish.ingredients
    }).then((response) => {

        state.flashSuccessMessage({
            message: response.data.message
        });

        state.hideModal({
            modal: 'dish'
        });

        emitter.emit('dishSaved', {
            dish: response.data.dish
        });
    }).catch((error) => {
        console.error(error);
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        }
        state.flashErrorMessage({
            message: trans('something_went_wrong')
        });
    });
}

function addIngredient() {
    axios.get(
        route('ingredients.get_ingredient', {
            id: selectedIngredientId.value
        })
    ).then((response) => {
        dish.ingredients.push(response.data);
    });
}

function removeIngredient(index) {
    dish.ingredients.splice(index, 1);
}

function format(num, digits = 3) {
    if (num === null || num === undefined || isNaN(num)) return '0.000';
    return Number(num).toFixed(digits);
}

// calculations
function calc(value, weight) {
    return format(value * weight);
}

function validate() {
    formIsValid.value = true;
    errors = initErrorsObject();
    if (dish.title.length === 0) {
        errors.title = trans('ingredient_title_is_required');
        formIsValid.value = false;
    }
}

// totals
const totals = computed(() => {
    let proteins = 0
    let fat = 0
    let carbohydrates = 0
    let calories = 0
    let brutto = 0
    let netto = 0

    dish.ingredients.forEach(i => {
        const nettoVal = Number(i.mass_netto) || 0
        const bruttoVal = Number(i.mass_brutto) || 0

        brutto += bruttoVal
        netto += nettoVal
        proteins += i.proteins * nettoVal
        fat += i.fat * nettoVal
        carbohydrates += i.carbohydrates * nettoVal
        calories += i.calories * nettoVal
    })

    return {
        mass_brutto: format(brutto),
        mass_netto: format(netto),
        proteins: format(proteins),
        fat: format(fat),
        carbohydrates: format(carbohydrates),
        calories: format(calories)
    }
})

function getIngredients() {
    axios.get(route('ingredients.json_list'), {
        params: {
            order_by_field: 'title',
            order_by_direction: 'asc',
            search_text: '',
            paginate_by: null
        }
    }).then((response) => {
        const formattedIngredients = [];
        response.data.forEach((ingredient) => {
            formattedIngredients.push({
                value: Number(ingredient.id),
                label: ingredient.title
            });
            ingredientsMap[ingredient.id] = ingredient;
        });
        ingredientsForSelect.value = formattedIngredients;
    });
}
</script>

<template>
    <div class="pt-1">
        <div class="space-y-5">
            <div>
                <label for="dish_title" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                    {{ trans('title') }}
                </label>
                <Input
                    id="dish_title"
                    type="text"
                    v-model="dish.title"
                    @keyup="validate"
                    :placeholder="trans('title')"
                    class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 focus:border-teal-500 focus:ring-teal-500"
                />
                <p v-if="errors.title.length > 0" class="mt-1 text-sm text-red-500">
                    {{ errors.title }}
                </p>
            </div>

            <div class="space-y-2">

                <div class="grid grid-cols-8 gap-2 bg-gray-400 text-gray-900 font-semibold p-3 rounded" v-if="dish.ingredients.length">
                    <div>{{ trans('ingredient') }}</div>
                    <div>{{ trans('mass_brutto') }}</div>
                    <div>{{ trans('yield') }}</div>
                    <div>{{ trans('proteins') }}</div>
                    <div>{{ trans('fat') }}</div>
                    <div>{{ trans('carbohydrates') }}</div>
                    <div>{{ trans('kilocalories') }}</div>
                    <div></div>
                </div>

                <draggable
                    v-model="dish.ingredients"
                    item-key="id"
                    handle=".drag-handle"
                    animation="200"
                >
                    <template #item="{ element, index }">
                        <div class="grid grid-cols-8 gap-2 items-center bg-gray-200 p-3 rounded text-gray-500">

                            <!-- drag handle -->
                            <div class="flex items-center gap-2">
                                <span class="drag-handle cursor-move text-gray-500">☰</span>
                                <span class="font-medium">{{ element.title }}</span>
                            </div>

                            <input type="number" v-model="element.mass_brutto" class="input w-full rounded border px-2 py-1 text-sm" />
                            <input type="number" v-model="element.mass_netto" class="input w-full rounded border px-2 py-1 text-sm" />

                            <div>{{ calc(element.proteins, element.mass_netto) }}</div>
                            <div>{{ calc(element.fat, element.mass_netto) }}</div>
                            <div>{{ calc(element.carbohydrates, element.mass_netto) }}</div>
                            <div>{{ calc(element.calories, element.mass_netto) }}</div>

                            <div class="flex justify-end">
                                <button @click="removeIngredient(index)" class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600">✕</button>
                            </div>
                        </div>
                    </template>
                </draggable>

                <div class="grid grid-cols-8 gap-2 bg-gray-500 text-white font-bold p-3 rounded" v-if="dish.ingredients.length">
                    <div>{{ trans('total') }}:</div>
                    <div>{{ totals.mass_brutto }}</div>
                    <div>{{ totals.mass_netto }}</div>
                    <div>{{ totals.proteins }}</div>
                    <div>{{ totals.fat }}</div>
                    <div>{{ totals.carbohydrates }}</div>
                    <div>{{ totals.calories }}</div>
                    <div></div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <VueSelect
                    :options="ingredientsForSelect"
                    v-model="selectedIngredientId"
                    label="label"
                    value-prop="value"
                    :placeholder="trans('select')"
                    input-id="ingredient-to-add"
                    class="max-w-[750px]"
                />
                <Button color="blue" @click="addIngredient">{{ trans('add_ingredient') }}</Button>
                <Button color="green" @click="state.callModal({modal: 'ingredient', objectInModal: {}})">{{ trans('create_ingredient') }}</Button>
            </div>
            <div class="mt-8 flex justify-end space-x-3 border-t border-gray-200 dark:border-gray-700 pt-4">
                <Button
                    color="blue"
                    :disabled="formIsValid === false"
                    @click="saveDish"
                >
                    {{ trans('save') }}
                </Button>
                <Button
                    color="gray"
                    @click="state.hideModal({ modal: 'dish' })"
                >
                    {{ trans('close') }}
                </Button>
            </div>
        </div>
    </div>
</template>
