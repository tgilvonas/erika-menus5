<script setup lang="ts">
import Button from '@/components/Button.vue';
import { Input } from '@/components/ui/input';
import { trans } from '@/helpers/translator';
import {ref, reactive, onMounted} from "vue";
import state from '@/state.js';
import axios from "axios";;
import { route } from 'ziggy-js';
import emitter from '@/eventBus.js';

const props = defineProps({
    ingredient: {
        type: Object,
        default: null
    }
});

const ingredient = reactive({...props.ingredient});
const calculateCaloriesAutomatically = ref(true);
const formIsValid = ref(true);

const associatedCalories = {
    proteins: 4,
    fat: 9,
    carbohydrates: 4
};
const ingredientFields = ['title', 'proteins', 'fat', 'carbohydrates', 'calories'];
const errors: Record<string, string> = initErrorsObject();

function calculateAndValidate() {
    formIsValid.value = true;
    const errors: Record<string, string> = initErrorsObject();

    if (typeof ingredient.title === 'undefined' || ingredient.title.length === 0) {
        errors.title = trans('ingredient_title_is_required');
        formIsValid.value = false;
    }

    for (let property of ingredientFields) {
        if (property !== 'title' && property !== 'id') {
            if (typeof ingredient[property] == 'undefined' || isNaN(convertToFloat(ingredient[property]))) {
                errors[property] = trans('value_must_be_numeric');
                formIsValid.value = false;
            }
        }
    }

    let sumOfMasses = 0;
    if (calculateCaloriesAutomatically.value) {
        ingredient.calories = 0;
    }
    for (let property in ingredient) {
        if (property !== 'title' && property !== 'calories' && property !== 'id') {
            sumOfMasses += convertToFloat(ingredient[property]);
            if (calculateCaloriesAutomatically.value) {
                ingredient.calories += convertToFloat(ingredient[property]) * associatedCalories[property];
            }
        }
    }

    if (sumOfMasses>1) {
        formIsValid.value = false;
        errors.carbohydrates = trans('sum_is_more_than_1_gram');
        errors.fat = trans('sum_is_more_than_1_gram');
        errors.proteins = trans('sum_is_more_than_1_gram');
    }
}

function saveIngredient() {
    calculateAndValidate()
    if (formIsValid.value) {
        state.modals.ingredient.modalContentLoaded = false;
        axios.post(route('ingredients.save'), {
            id: ingredient.id,
            proteins: ingredient.proteins,
            fat: ingredient.fat,
            carbohydrates: ingredient.carbohydrates,
            calories: ingredient.calories,
            title: ingredient.title
        }).then(function(response){
            state.modals.ingredient.modalContentLoaded = true;
            if (response.data.result === 'ingredient_exists') {
                errors.title = trans('ingredient_already_exists');
                formIsValid.value = false;
            } else {
                state.hideModal({modal: 'ingredient'});
                state.flashSuccessMessage({message: response.data.message});
                ingredient.id = response.data?.ingredient?.id;
                emitter.emit('ingredientSaved', {ingredient});
            }
        });
    }
}

function convertToFloat(value: string | number) {
    if (typeof value === 'number') {
        return value;
    }
    const normalized = value.replace(',', '.');
    const parsed = parseFloat(normalized);
    return isNaN(parsed) ? 0 : parsed;
}

function initErrorsObject(): Record<string, string> {
    return {
        title: '',
        proteins: '',
        fat: '',
        carbohydrates: '',
        calories: ''
    }
}
</script>

<template>
    <div class="pt-1">
        <div class="space-y-5">
            <div>
                <label for="ingredient_title" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                    {{ trans('title') }}
                </label>
                <Input
                    id="ingredient_title"
                    type="text"
                    v-model="ingredient.title"
                    @keyup="calculateAndValidate"
                    :placeholder="trans('title')"
                    class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 focus:border-teal-500 focus:ring-teal-500"
                />
                <p v-if="errors.title.length > 0" class="mt-1 text-sm text-red-500">
                    {{ errors.title }}
                </p>
            </div>

            <div class="text-base font-semibold text-gray-800 dark:text-gray-200">
                {{ trans('contents_in_1_gram') }}:
            </div>

            <div>
                <label for="proteins" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                    {{ trans('proteins') }}
                </label>
                <Input
                    id="proteins"
                    type="number"
                    v-model="ingredient.proteins"
                    @keyup="calculateAndValidate"
                    :placeholder="trans('proteins')"
                    min="0"
                    max="1"
                    step="0.0001"
                    class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 focus:border-teal-500 focus:ring-teal-500"
                />
                <div v-if="errors.proteins.length > 0" class="mt-1 text-sm text-red-500">
                    {{ errors.proteins }}
                </div>
            </div>
            <div>
                <label for="fat" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                    {{ trans('fat') }}
                </label>
                <Input
                    id="fat"
                    type="number"
                    v-model="ingredient.fat"
                    @keyup="calculateAndValidate"
                    :placeholder="trans('fat')"
                    max="1"
                    step="0.0001"
                    class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 focus:border-teal-500 focus:ring-teal-500"
                />
                <div v-if="errors.fat.length > 0" class="mt-1 text-sm text-red-500">
                    {{ errors.fat }}
                </div>
            </div>
            <div>
                <label for="carbohydrates" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                    {{ trans('carbohydrates') }}
                </label>
                <Input
                    id="carbohydrates"
                    type="number"
                    v-model="ingredient.carbohydrates"
                    @keyup="calculateAndValidate"
                    :placeholder="trans('carbohydrates')"
                    min="0"
                    max="1"
                    step="0.0001"
                    class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 focus:border-teal-500 focus:ring-teal-500"
                />
                <div v-if="errors.carbohydrates.length > 0" class="mt-1 text-sm text-red-500">
                    {{ errors.carbohydrates }}
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <input
                    id="calculate_automatically"
                    type="checkbox"
                    v-model="calculateCaloriesAutomatically"
                    @change="calculateAndValidate"
                    class="rounded border-gray-300 text-teal-600 focus:ring-teal-500"
                />
                <label for="calculate_automatically" class="text-sm text-gray-700 dark:text-gray-200">
                    {{ trans('calculate_kilocalories_automatically') }}
                </label>
            </div>
            <div>
                <label for="calories" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                    {{ trans('kilocalories') }}
                </label>
                <Input
                    id="calories"
                    type="number"
                    v-model="ingredient.calories"
                    @keyup="calculateAndValidate"
                    :readonly="calculateCaloriesAutomatically"
                    :placeholder="trans('kilocalories')"
                    min="0"
                    max="1"
                    step="0.0001"
                    class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 focus:border-teal-500 focus:ring-teal-500 disabled:opacity-60 disabled:cursor-not-allowed"
                />
                <div
                    v-if="errors.calories.length > 0 && !calculateCaloriesAutomatically"
                    class="mt-1 text-sm text-red-500"
                >
                    {{ errors.calories }}
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="mt-8 flex justify-end space-x-3 border-t border-gray-200 dark:border-gray-700 pt-4">
            <Button
                color="blue"
                :disabled="formIsValid === false"
                @click="saveIngredient"
            >
                {{ trans('save') }}
            </Button>

            <Button
                color="gray"
                @click="state.hideModal({ modal: 'ingredient' })"
            >
                {{ trans('close') }}
            </Button>
        </div>
    </div>
</template>
