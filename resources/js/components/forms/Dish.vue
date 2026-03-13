<script setup lang="ts">
import Button from '@/components/Button.vue';
import { Input } from '@/components/ui/input';
import { trans } from '@/helpers/translator';
import state from '@/state.js';
import axios from "axios";
import { route } from 'ziggy-js';
import emitter from '@/eventBus.js';
import {reactive, ref} from "vue";

const props = defineProps({
    dish: {
        type: Object,
        default: null
    }
});

const dish = reactive({...props.dish});
const selectedIngredient = reactive({});
const formIsValid = ref(true);

const errors: Record<string, string> = initErrorsObject();

// @TODO: implement
function initErrorsObject(): Record<string, string> {
    return {
        title: '',
    }
}

// @TODO: implement
function calculateAndValidate() {

}

// @TODO: implement
function saveDish() {

}

// @TODO: implement
function addIngredient() {

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
                    @keyup="calculateAndValidate"
                    :placeholder="trans('title')"
                    class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 focus:border-teal-500 focus:ring-teal-500"
                />
                <p v-if="errors.title.length > 0" class="mt-1 text-sm text-red-500">
                    {{ errors.title }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <div>Select input should be here</div>
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
