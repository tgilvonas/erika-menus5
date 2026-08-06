<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import Button from '@/components/Button.vue';
import Paginator from '@/components/Paginator.vue';
import Modal from '@/components/Modal.vue';
import DeleteDialog from '@/components/DeleteDialog.vue';
import FlashMessage from '@/components/FlashMessage.vue';
import UserForm from '@/components/forms/User.vue';
import { trans } from '@/helpers/translator';
import { ref, onMounted, onBeforeUnmount } from 'vue';
import axios from 'axios';
import { route } from 'ziggy-js';
import emitter from '@/eventBus.js';
import state from '@/state.js';

const users = ref([]);
const pagination = ref([]);
const searchText = ref('');
const loading = ref(false);

onMounted(() => {
    getUsersList(1);
    emitter.on('paginatorClicked', handlePaginatorClick);
    emitter.on('userSaved', handleListChanged);
    emitter.on('objectDeleted', handleListChanged);
});

onBeforeUnmount(() => {
    emitter.off('paginatorClicked', handlePaginatorClick);
    emitter.off('userSaved', handleListChanged);
    emitter.off('objectDeleted', handleListChanged);
});

function handlePaginatorClick(payload: object) {
    getUsersList(payload.page);
}

function handleListChanged() {
    getUsersList(1);
}

function getUsersList(page: number) {
    loading.value = true;
    axios.get(route('users.json_list'), {
        params: {
            order_by_field: 'name',
            order_by_direction: 'asc',
            page: page,
            search_text: searchText.value,
            paginate_by: 10,
        },
    }).then(function (response) {
        loading.value = false;
        users.value = response.data.data;
        pagination.value = response.data.links;
    });
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: trans('users'),
        href: dashboard().url,
    },
];
</script>

<template>
    <Head :title="trans('users')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <FlashMessage type="success" />
            <FlashMessage type="error" />

            <Modal modal-name="user">
                <template #modal_title>
                    {{ trans('user') }}
                </template>
                <template #content>
                    <div>
                        <UserForm :user="state.modals.user.objectInModal" />
                    </div>
                </template>
            </Modal>

            <Modal modal-name="objectToDelete">
                <template #modal_title>
                    {{ trans('delete_record') }}
                </template>
                <template #content>
                    <div>
                        <DeleteDialog :delete-url="route('users.delete').toString()" />
                    </div>
                </template>
            </Modal>

            <div class="mb-3">
                <Button @click="state.callModal({ modal: 'user', objectInModal: {} })" color="green">
                    {{ trans('create_new') }}
                </Button>
            </div>

            <div class="flex items-center space-x-2 pb-3">
                <label for="search" class="">
                    {{ trans('user') }}:
                </label>
                <input
                    id="search"
                    class="block w-full rounded-lg border-gray-300 bg-white p-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                    v-model="searchText"
                    :placeholder="trans('enter_text_for_search')"
                    @keyup="getUsersList(1)"
                />
            </div>

            <table class="table-auto border-collapse w-full" v-if="users.length">
                <thead>
                    <tr>
                        <th class="border p-2">{{ trans('name') }}</th>
                        <th class="border p-2">{{ trans('email') }}</th>
                        <th class="border p-2">{{ trans('role') }}</th>
                        <th class="border p-2"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in users" :key="user.id">
                        <td class="border p-2">{{ user.name }}</td>
                        <td class="border p-2">{{ user.email }}</td>
                        <td class="border p-2">{{ user.roles }}</td>
                        <td class="border p-2">
                            <Button @click="state.callModal({ modal: 'user', objectInModal: user })">
                                {{ trans('edit') }}
                            </Button>
                            <Button
                                @click="state.callModal({ modal: 'objectToDelete', objectInModal: { ...user, title: user.name || user.email } })"
                                color="red"
                            >
                                {{ trans('delete') }}
                            </Button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-else-if="!loading">
                {{ trans('no_records') }}
            </div>

            <div class="">
                <Paginator :pagination="pagination" />
            </div>
        </div>
    </AppLayout>
</template>
