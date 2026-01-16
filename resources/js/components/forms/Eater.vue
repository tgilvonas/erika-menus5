<script setup>
import {ref, reactive, onMounted} from 'vue';
import { trans } from '@/helpers/translator';
import Button from '@/components/Button.vue';
import state from '@/state.js';
import axios from 'axios';
import { route } from 'ziggy-js';
import Input from "@/components/ui/input/Input.vue";
import VueSelect from "vue3-select-component";
import "vue3-select-component/styles";

const props = defineProps({
    eater: {
        type: Object,
        default: () => ({}),
    },
});

const eater = reactive({
    id: props.eater.id ?? null,
    name: props.eater.name ?? '',
    diet_type_id: props.eater.diet_type_id ?? null
});

const errors = reactive({
    name: [],
});

const formIsValid = ref(true);

const dietTypes = ref([]);

onMounted(() => {
  getDietTypes();
});

function validateEater() {
  formIsValid.value = true;
  errors.name = [];
  errors.dietType = [];

  if (!eater.name || eater.name.length === 0) {
    errors.name.push(trans('required'));
    formIsValid.value = false;
  }
  if (eater.diet_type_id === null) {
    errors.dietType.push(trans('required'));
    formIsValid.value = false;
  }
}

function saveEater() {
  validateEater();
}

function getDietTypes() {
    axios.get(route('diet_types.json_list'), {
        params: {
            order_by_field: 'title',
            order_by_direction: 'asc',
            page: 1,
            search_text: '',
            paginate_by: 200
        }
    }).then(function(response){
        const formattedDietTypes = [];
        response.data.data.forEach((dietType) => {
            const formattedDietType = {
                value: dietType.id,
                label: dietType.title
            }
            formattedDietTypes.push(formattedDietType)
        })
        dietTypes.value = formattedDietTypes;
    });
}

</script>

<template>
    <div class="space-y-6">
        <div>
            <label
                for="name"
                class="block text-sm font-medium text-gray-700 dark:text-gray-200"
            >
                {{ trans('name') }}
            </label>
            <Input
                id="name"
                type="text"
                v-model="eater.name"
                @keyup="validateEater"
                class="mt-1 w-full rounded-lg border-gray-300 shadow-sm
                       focus:border-blue-500 focus:ring-blue-500
                       dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            />
            <div
                v-if="errors.name.length"
                class="mt-1 text-sm text-red-600"
            >
                {{ errors.name[0] }}
            </div>

            <label
                for="diet_type"
                class="block text-sm font-medium text-gray-700 dark:text-gray-200 mt-3 mb-1"
            >
              {{ trans('diet') }}
            </label>
            <VueSelect :options="dietTypes" v-model="eater.diet_type_id" :placeholder="trans('select')" input-id="diet_type" />
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t">
            <Button
                color="blue"
                :disabled="formIsValid === false"
                @click="saveEater"
            >
                {{ trans('save') }}
            </Button>
            <Button
                color="gray"
                @click="state.hideModal({ modal: 'eater' })"
            >
                {{ trans('close') }}
            </Button>
        </div>
    </div>
</template>
