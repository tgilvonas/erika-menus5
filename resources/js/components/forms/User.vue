<script setup lang="ts">
import { reactive, ref, watch } from 'vue';
import { trans } from '@/helpers/translator';
import Button from '@/components/Button.vue';
import state from '@/state.js';
import axios from 'axios';
import { route } from 'ziggy-js';
import { Input } from '@/components/ui/input';
import emitter from '@/eventBus.js';

const props = defineProps({
    user: {
        type: Object,
        default: () => ({}),
    },
});

const user = reactive({
    id: props.user.id ?? null,
    name: props.user.name ?? '',
    email: props.user.email ?? '',
    roles: props.user.roles ?? 'ROLE_DIETOLOGIST',
    password: '',
    password_confirmation: '',
});

watch(
    () => props.user,
    (next) => {
        user.id = next?.id ?? null;
        user.name = next?.name ?? '';
        user.email = next?.email ?? '';
        user.roles = next?.roles ?? 'ROLE_DIETOLOGIST';
        user.password = '';
        user.password_confirmation = '';
    },
    { immediate: true, deep: true }
);

const errors = reactive<Record<string, string[]>>({
    name: [],
    email: [],
    roles: [],
    password: [],
    password_confirmation: [],
});

const formIsValid = ref(true);
const roleOptions = [
    { value: 'ROLE_SUPERADMIN', label: trans('superadmin') },
    { value: 'ROLE_DIETOLOGIST', label: trans('dietologist') },
];

function validateUser() {
    formIsValid.value = true;
    errors.name = [];
    errors.email = [];
    errors.roles = [];
    errors.password = [];
    errors.password_confirmation = [];

    if (!user.name) {
        errors.name.push(trans('required'));
        formIsValid.value = false;
    }

    if (!user.email) {
        errors.email.push(trans('required'));
        formIsValid.value = false;
    }

    if (!user.roles) {
        errors.roles.push(trans('required'));
        formIsValid.value = false;
    }

    const passwordProvided = !!user.password || !!user.password_confirmation;

    if (passwordProvided) {
        if (!user.password) {
            errors.password.push(trans('required'));
            formIsValid.value = false;
        }

        if (!user.password_confirmation) {
            errors.password_confirmation.push(trans('required'));
            formIsValid.value = false;
        }

        if (user.password && user.password_confirmation && user.password !== user.password_confirmation) {
            errors.password.push(trans('confirm_password'));
            errors.password_confirmation.push(trans('confirm_password'));
            formIsValid.value = false;
        }
    }

    if (!user.id && !user.password) {
        errors.password.push(trans('required'));
        formIsValid.value = false;
    }
}

function saveUser() {
    validateUser();

    if (!formIsValid.value) {
        return;
    }

    const payload = {
        id: user.id,
        name: user.name,
        email: user.email,
        roles: user.roles,
        password: user.password,
        password_confirmation: user.password_confirmation,
    };

    axios.post(route('users.save'), payload)
        .then((response) => {
            state.hideModal({ modal: 'user' });
            state.flashSuccessMessage({ message: response.data.message });
            emitter.emit('userSaved', { user });
        })
        .catch((error) => {
            if (error.response?.data?.errors) {
                Object.entries(error.response.data.errors).forEach(([field, messages]) => {
                    if (Array.isArray(messages) && errors[field] !== undefined) {
                        errors[field] = messages;
                    }
                });
            }

            formIsValid.value = false;
        });
}
</script>

<template>
    <div class="space-y-6">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                {{ trans('name') }}
            </label>
            <Input
                id="name"
                type="text"
                v-model="user.name"
                @keyup="validateUser"
                class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            />
            <div v-if="errors.name.length" class="mt-1 text-sm text-red-600">
                {{ errors.name[0] }}
            </div>

            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mt-3 mb-1">
                {{ trans('email') }}
            </label>
            <Input
                id="email"
                type="email"
                v-model="user.email"
                @keyup="validateUser"
                class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            />
            <div v-if="errors.email.length" class="mt-1 text-sm text-red-600">
                {{ errors.email[0] }}
            </div>

            <label for="roles" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mt-3 mb-1">
                {{ trans('role') }}
            </label>
            <select
                id="roles"
                v-model="user.roles"
                @change="validateUser"
                class="mt-1 block w-full rounded-lg border-gray-300 bg-white p-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            >
                <option disabled value="">{{ trans('select') }}</option>
                <option v-for="option in roleOptions" :key="option.value" :value="option.value">
                    {{ option.label }}
                </option>
            </select>
            <div v-if="errors.roles.length" class="mt-1 text-sm text-red-600">
                {{ errors.roles[0] }}
            </div>

            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mt-3 mb-1">
                {{ trans('password') }}
            </label>
            <Input
                id="password"
                type="password"
                v-model="user.password"
                @keyup="validateUser"
                class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            />
            <div v-if="errors.password.length" class="mt-1 text-sm text-red-600">
                {{ errors.password[0] }}
            </div>

            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mt-3 mb-1">
                {{ trans('confirm_password') }}
            </label>
            <Input
                id="password_confirmation"
                type="password"
                v-model="user.password_confirmation"
                @keyup="validateUser"
                class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            />
            <div v-if="errors.password_confirmation.length" class="mt-1 text-sm text-red-600">
                {{ errors.password_confirmation[0] }}
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t">
            <Button color="blue" :disabled="formIsValid === false" @click="saveUser">
                {{ trans('save') }}
            </Button>
            <Button color="gray" @click="state.hideModal({ modal: 'user' })">
                {{ trans('close') }}
            </Button>
        </div>
    </div>
</template>
