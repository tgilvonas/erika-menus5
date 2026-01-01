<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import Button from '@/components/Button.vue';
import Paginator from '@/components/Paginator.vue';
import Modal from '@/components/Modal.vue';
import DeleteDialog from '@/components/DeleteDialog.vue';
import FlashMessage from "@/components/FlashMessage.vue";
import { trans } from '@/helpers/translator';
import {ref, onMounted, onBeforeUnmount} from "vue";
import axios from "axios";
import { route } from 'ziggy-js';
import emitter from '@/eventBus.js';
import state from '@/state.js';
import Mealtime from "@/components/forms/Mealtime.vue";

const mealtimes = ref([]);
const pagination = ref([]);
const searchText = ref('');
const loading = ref(false);

onMounted(() => {
    getMealtimesList(1);
    emitter.on('mealtimeSaved', handleListChanged);
    emitter.on('objectDeleted', handleListChanged);
});
onBeforeUnmount(() => {
    emitter.off('mealtimeSaved', handleListChanged);
    emitter.off('objectDeleted', handleListChanged);
});

function handleListChanged() {
    getMealtimesList(1);
}

function getMealtimesList(page) {
    loading.value = true;
    axios.get(route('mealtimes.json_list'), {
        params: {
            order_by_field: 'title',
            order_by_direction: 'asc',
            page: page,
            search_text: searchText.value,
            paginate_by: 10
        }
    }).then(function(response){
        loading.value = false;
        mealtimes.value = response.data.data;
    });
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: trans('mealtimes'),
        href: dashboard().url,
    },
];
</script>

<template>
    <Head :title="trans('mealtimes')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <FlashMessage type="success" />
            <FlashMessage type="error" />

            <Modal modal-name="mealtime">
                <template #modal_title>
                    {{ trans('mealtime') }}
                </template>

                <template #content>
                    <div>
                        <Mealtime :mealtime="state.modals.mealtime.objectInModal" />
                    </div>
                </template>
            </Modal>

            <Modal modal-name="objectToDelete">
                <template #modal_title>
                    {{ trans('delete_record') }}
                </template>

                <template #content>
                    <div>
                        <DeleteDialog :delete-url="route('mealtimes.delete').toString()" />
                    </div>
                </template>
            </Modal>

            <div class="mb-3">
                <Button
                    color="green"
                    @click="state.callModal({ modal: 'mealtime', objectInModal: {} })"
                >
                    {{ trans('create_new') }}
                </Button>
            </div>

            <table
                v-if="mealtimes.length"
                class="table-auto border-collapse w-full"
            >
                <thead>
                <tr>
                    <th class="border p-2">{{ trans('mealtime') }}</th>
                    <th class="border p-2">{{ trans('from_percent') }}</th>
                    <th class="border p-2">{{ trans('to_percent') }}</th>
                    <th class="border p-2"></th>
                </tr>
                </thead>

                <tbody>
                <tr
                    v-for="(mealtime, key) in mealtimes"
                    :key="key"
                >
                    <td class="border p-2">
                        {{ mealtime.title }}
                    </td>

                    <td class="border p-2 text-right">
                        {{ mealtime.percent_from.toFixed(2) }}
                    </td>

                    <td class="border p-2 text-right">
                        {{ mealtime.percent_to.toFixed(2) }}
                    </td>

                    <td class="border p-2">
                        <Button
                            @click="state.callModal({ modal: 'mealtime', objectInModal: mealtime })"
                        >
                            {{ trans('edit') }}
                        </Button>

                        <Button
                            color="red"
                            @click="state.callModal({ modal: 'objectToDelete', objectInModal: mealtime })"
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
        </div>
    </AppLayout>
</template>
