<script setup>
import { ref, reactive } from 'vue';
import { trans } from '@/helpers/translator';
import Button from '@/components/Button.vue';
import state from '@/state.js';
import axios from 'axios';
import { route } from 'ziggy-js';
import Input from "@/components/ui/input/Input.vue";

const props = defineProps({
    eater: {
        type: Object,
        default: () => ({}),
    },
});

const eater = reactive({
    id: props.eater.id ?? null,
    name: props.eater.name ?? '',
});

const errors = reactive({
    name: [],
});

const formIsValid = ref(true);

function validateEater() {

}

function saveEater() {

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
