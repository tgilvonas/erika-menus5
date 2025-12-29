<script setup lang="ts">
import Button from '@/components/Button.vue';
import { Input } from '@/components/ui/input';
import { trans } from '@/helpers/translator';
import {ref, reactive, onMounted} from "vue";
import state from '@/state.js';
import axios from "axios";
import { route } from 'ziggy-js';
import emitter from '@/eventBus.js';

const props = defineProps({
  dietType: {
    type: Object,
    default: null
  }
});

const dietType = reactive({...props.dietType});

const formIsValid = ref(true);

const errors = reactive({
  title: [],
  proteins_min: [],
  proteins_max: [],
  fat_min: [],
  fat_max: [],
  carbohydrates_min: [],
  carbohydrates_max: [],
  calories_min: [],
  calories_max: []
});

const dietTypeFields = [
  'title',
  'proteins_min',
  'proteins_max',
  'fat_min',
  'fat_max',
  'carbohydrates_min',
  'carbohydrates_max',
  'calories_min',
  'calories_max',
];

function validateDietType() {
  formIsValid.value = true;
  for (let errorList in errors) {
    errors[errorList] = [];
  }
  for (let property of dietTypeFields) {
    if (property !== 'title' && (typeof dietType[property] === 'undefined' || isNaN(dietType[property]))) {
      formIsValid.value = false;
      errors[property].push(trans('must_be_numeric'));
    }
    if (typeof dietType[property] === 'undefined' || dietType[property].length === 0) {
      formIsValid.value = false;
      errors[property].push(trans('required'));
    }
  }
}

function saveDietType() {

  validateDietType();

  if (formIsValid.value) {
    state.modals.dietType.modalContentLoaded = false;
    let dietTypePostObject = {
      id: dietType.id,
    }
    for (let field of dietTypeFields) {
      dietTypePostObject[field] = dietType[field];
    }
    axios.post(route('diet_types.save'), dietTypePostObject).then(function(response){
      state.modals.dietType.modalContentLoaded = true;
      state.hideModal({modal: 'dietType'});
      dietType.id = response.data.diet_type_id;
      state.savedObject = dietType;
      state.flashSuccessMessage({message: response.data.message});
      emitter.emit('dietTypeSaved', {dietType});
    }).catch(error => {
      for (let key in error.response?.data?.errors) {
        errors[key] = error.response?.data?.errors[key];
      }
      formIsValid.value = false;
      state.modals.dietType.modalContentLoaded = true;
    });
  }
}

</script>

<template>
  <div id="form_to_edit_diet_type" class="space-y-6">
    <div>
      <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
        {{ trans('diet_title') }}
      </label>
      <Input
          id="title"
          type="text"
          v-model="dietType.title"
          @keyup="validateDietType"
          class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
      />
      <div v-if="errors.title.length" class="mt-1 text-sm text-red-600">
        {{ errors.title[0] }}
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
          {{ trans('proteins_min') }}
        </label>
        <Input
            type="number"
            v-model="dietType.proteins_min"
            @keyup="validateDietType"
            class="mt-1 w-full rounded-lg border-gray-300 shadow-sm"
        />
        <div v-if="errors.proteins_min.length" class="mt-1 text-sm text-red-600">
          {{ errors.proteins_min[0] }}
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
          {{ trans('proteins_max') }}
        </label>
        <Input
            type="number"
            v-model="dietType.proteins_max"
            @keyup="validateDietType"
            class="mt-1 w-full rounded-lg border-gray-300 shadow-sm"
        />
        <div v-if="errors.proteins_max.length" class="mt-1 text-sm text-red-600">
          {{ errors.proteins_max[0] }}
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
          {{ trans('fat_min') }}
        </label>
        <Input
            type="number"
            v-model="dietType.fat_min"
            @keyup="validateDietType"
            class="mt-1 w-full rounded-lg border-gray-300 shadow-sm"
        />
        <div v-if="errors.fat_min.length" class="mt-1 text-sm text-red-600">
          {{ errors.fat_min[0] }}
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
          {{ trans('fat_max') }}
        </label>
        <Input
            type="number"
            v-model="dietType.fat_max"
            @keyup="validateDietType"
            class="mt-1 w-full rounded-lg border-gray-300 shadow-sm"
        />
        <div v-if="errors.fat_max.length" class="mt-1 text-sm text-red-600">
          {{ errors.fat_max[0] }}
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
          {{ trans('carbohydrates_min') }}
        </label>
        <Input
            type="number"
            v-model="dietType.carbohydrates_min"
            @keyup="validateDietType"
            class="mt-1 w-full rounded-lg border-gray-300 shadow-sm"
        />
        <div v-if="errors.carbohydrates_min.length" class="mt-1 text-sm text-red-600">
          {{ errors.carbohydrates_min[0] }}
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
          {{ trans('carbohydrates_max') }}
        </label>
        <Input
            type="number"
            v-model="dietType.carbohydrates_max"
            @keyup="validateDietType"
            class="mt-1 w-full rounded-lg border-gray-300 shadow-sm"
        />
        <div v-if="errors.carbohydrates_max.length" class="mt-1 text-sm text-red-600">
          {{ errors.carbohydrates_max[0] }}
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
          {{ trans('kilocalories_min') }}
        </label>
        <Input
            type="number"
            v-model="dietType.calories_min"
            @keyup="validateDietType"
            class="mt-1 w-full rounded-lg border-gray-300 shadow-sm"
        />
        <div v-if="errors.calories_min.length" class="mt-1 text-sm text-red-600">
          {{ errors.calories_min[0] }}
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
          {{ trans('kilocalories_min') }}
        </label>
        <Input
            type="number"
            v-model="dietType.calories_max"
            @keyup="validateDietType"
            class="mt-1 w-full rounded-lg border-gray-300 shadow-sm"
        />
        <div v-if="errors.calories_max.length" class="mt-1 text-sm text-red-600">
          {{ errors.calories_max[0] }}
        </div>
      </div>
    </div>

    <div class="flex justify-end gap-3 pt-4 border-t">
      <Button
          color="blue"
          :disabled="formIsValid === false"
          @click="saveDietType"
      >
        {{ trans('save') }}
      </Button>
      <Button
          color="gray"
          @click="state.hideModal({ modal: 'dietType' })"
      >
        {{ trans('close') }}
      </Button>
    </div>

  </div>
</template>
