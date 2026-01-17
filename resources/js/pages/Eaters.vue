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
import Eater from "@/components/forms/Eater.vue";
import {Input} from "@/components/ui/input";
import DietType from "@/components/forms/DietType.vue";

const eaters = ref([]);
const pagination = ref([]);
const searchText = ref('');
const loading = ref(false);

onMounted(() => {
    getEatersList(1);
    emitter.on('paginatorClicked', handlePaginatorClick);
    emitter.on('eaterSaved', handleListChanged);
    emitter.on('objectDeleted', handleListChanged);
});
onBeforeUnmount(() => {
    emitter.off('paginatorClicked', handlePaginatorClick);
    emitter.off('eaterSaved', handleListChanged);
    emitter.off('objectDeleted', handleListChanged);
});

function handlePaginatorClick(payload: object) {
    getEatersList(payload.page);
}
function handleListChanged() {
    getEatersList(1);
}

function getEatersList(page: number) {
    loading.value = true;
    axios.get(route('eaters.json_list'), {
        params: {
            order_by_field: 'name',
            order_by_direction: 'asc',
            page: page,
            search_text: searchText.value,
            paginate_by: 10
        }
    }).then(function(response){
        loading.value = false;
        eaters.value = response.data.data;
        pagination.value = response.data.links;
    })
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: trans('eaters'),
        href: dashboard().url,
    },
];
</script>

<template>
    <Head :title="trans('eaters')" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <FlashMessage type="success"></FlashMessage>
            <FlashMessage type="error"></FlashMessage>
            <Modal modal-name="eater">
                <template #modal_title>
                    {{ trans('eater') }}
                </template>
                <template #content>
                    <div>
                        <Eater :eater="state.modals.eater.objectInModal" />
                    </div>
                </template>
            </Modal>
            <Modal modal-name="objectToDelete">
                <template #modal_title>
                    {{ trans('delete_record') }}
                </template>
                <template #content>
                    <div>
                        <DeleteDialog :delete-url="route('eaters.delete').toString()" />
                    </div>
                </template>
            </Modal>
            <Modal modal-name="dietType">
                <template #modal_title>
                    {{ trans('diet') }}
                </template>
                <template #content>
                    <div>
                        <DietType :diet-type="state.modals.dietType.objectInModal" />
                    </div>
                </template>
            </Modal>
            <div class="mb-3">
                <Button @click="state.callModal({modal: 'eater', objectInModal: {}})" color="green">
                    {{ trans('create_new') }}
                </Button>
            </div>
            <div class="flex items-center space-x-2 pb-3">
                <label for="search" class="">
                    {{ trans('eater')}}:
                </label>
                <Input
                    class="block w-full"
                    v-model="searchText"
                    :placeholder="trans('enter_text_for_search')"
                    @keyup="getEatersList"
                />
            </div>
            <table class="table-auto border-collapse w-full" v-if="eaters.length">
                <thead>
                    <tr>
                        <th class="border p-2">
                            {{ trans('eater') }}
                        </th>
                        <th class="border p-2">
                            {{ trans('diet') }}
                        </th>
                        <th class="border p-2">

                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="eater in eaters">
                        <td class="border p-2">{{ eater.name }}</td>
                        <td class="border p-2">{{ eater.diet_type }}</td>
                        <td class="border p-2">
                            <Button @click="state.callModal({modal: 'eater', objectInModal: eater})">
                                {{ trans('edit') }}
                            </Button>
                            <Button @click="state.callModal({modal: 'objectToDelete', objectInModal: eater})" color="red">
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
