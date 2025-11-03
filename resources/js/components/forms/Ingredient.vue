<script setup lang="ts">
import Button from '@/components/Button.vue'
import { Input } from '@/components/ui/input';
import { trans } from '@/helpers/translator';
import {ref} from "vue";
import state from '@/state.js'

defineProps({
    ingredient: {
        type: Object,
        default: {}
    }
})

const calculateCaloriesAutomatically = ref(true)
const formIsValid = ref(true)

const associatedCalories = {
    proteins: 4,
    fat: 9,
    carbohydrates: 4
}

function calculateAndValidate()
{

}

function saveIngredient() {

}

const ingredientFields = ['title', 'proteins', 'fat', 'carbohydrates', 'calories']
const errors = {
    title: '',
    proteins: '',
    fat: '',
    carbohydrates: '',
    calories: ''
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
                <p v-if="errors.proteins.length > 0" class="mt-1 text-sm text-red-500">
                    {{ errors.proteins }}
                </p>
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
                <p v-if="errors.fat.length > 0" class="mt-1 text-sm text-red-500">
                    {{ errors.fat }}
                </p>
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
                <p v-if="errors.carbohydrates.length > 0" class="mt-1 text-sm text-red-500">
                    {{ errors.carbohydrates }}
                </p>
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
                <p
                    v-if="errors.calories.length > 0 && !calculateCaloriesAutomatically"
                    class="mt-1 text-sm text-red-500"
                >
                    {{ errors.calories }}
                </p>
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
