<script setup lang="ts">
import { trans } from '@/helpers/translator';
import {ref, reactive} from "vue";
import state from '@/state.js';
import axios from "axios";
import { route } from 'ziggy-js';
import emitter from '@/eventBus.js';
import Button from "@/components/Button.vue";

const props = defineProps({
    mealtime: {
        type: Object,
        default: null
    }
});

const mealtime = reactive({...props.mealtime});

const formIsValid = ref(true);

const errors = reactive({
    title: [],
    percent_from: [],
    percent_to: []
});

const mealtimeFields = ['title', 'percent_from', 'percent_to'];

function validateMealtime() {
    formIsValid.value = true;
    for (let errorList in errors) {
        errors[errorList] = [];
    }
    for (let property of mealtimeFields) {
        if (property != 'id') {
            if (property != 'title' && isNaN(mealtime[property])) {
                formIsValid.value = false;
                errors[property].push(trans('must_be_numeric'));
            }
            if (typeof mealtime[property] == "undefined" || mealtime[property].length == 0) {
                formIsValid.value = false;
                errors[property].push(trans('required'));
            }
        }
    }
}

function saveMealtime() {
    validateMealtime();
    if (formIsValid.value) {
        state.modals.mealtime.modalContentLoaded = false;
        axios.post(route('mealtimes.save'), {
            id: mealtime.id,
            title: mealtime.title,
            percent_from: mealtime.percent_from,
            percent_to: mealtime.percent_to
        }).then(function(response){
            state.hideModal({modal: 'mealtime'});
            state.savedObject = mealtime;
            state.flashSuccessMessage({message: response.data.message});
            emitter.emit('mealtimeSaved', {mealtime});
        }).catch(error => {
            for (let key in error.response.data.errors) {
                errors[key] = error.response.data.errors[key];
            }
            formIsValid.value = false;
            state.modals.mealtime.modalContentLoaded = true;
        });
    }
}

</script>

<template>
    <div id="form_to_edit_mealtime" class="flex flex-col gap-6">
        <div class="space-y-4">
            <div>
                <label
                    for="title"
                    class="block text-sm font-medium text-gray-700 dark:text-gray-200"
                >
                    {{ trans('mealtime_title') }}
                </label>
                <input
                    id="title"
                    type="text"
                    name="title"
                    v-model="mealtime.title"
                    @keyup="validateMealtime"
                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                           focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                />
                <div
                    v-if="errors.title.length > 0"
                    class="mt-1 text-sm text-red-500"
                >
                    {{ errors.title[0] }}
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label
                        for="percent_from"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-200"
                    >
                        {{ trans('from_percent') }}
                    </label>
                    <input
                        id="percent_from"
                        type="number"
                        min="0"
                        max="100"
                        step="0.01"
                        name="percent_from"
                        v-model="mealtime.percent_from"
                        @keyup="validateMealtime"
                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                               focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    />
                    <div
                        v-if="errors.percent_from.length > 0"
                        class="mt-1 text-sm text-red-500"
                    >
                        {{ errors.percent_from[0] }}
                    </div>
                </div>

                <div>
                    <label
                        for="percent_to"
                        class="block text-sm font-medium text-gray-700 dark:text-gray-200"
                    >
                        {{ trans('to_percent') }}
                    </label>

                    <input
                        id="percent_to"
                        type="number"
                        min="0"
                        max="100"
                        step="0.01"
                        name="percent_to"
                        v-model="mealtime.percent_to"
                        @keyup="validateMealtime"
                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                               focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                    />
                    <div
                        v-if="errors.percent_to.length > 0"
                        class="mt-1 text-sm text-red-500"
                    >
                        {{ errors.percent_to[0] }}
                    </div>
                </div>
            </div>
        </div>
        <div class="flex justify-end gap-3 pt-4 border-t">
            <Button
                color="blue"
                :disabled="formIsValid === false"
                @click="saveMealtime"
            >
                {{ trans('save') }}
            </Button>
            <Button
                color="gray"
                @click="state.hideModal({ modal: 'mealtime' })"
            >
                {{ trans('close') }}
            </Button>
        </div>
    </div>
</template>
