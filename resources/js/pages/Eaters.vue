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

const eaters = ref([]);
const pagination = ref([]);
const searchText = ref('');
const loading = ref(false);

onMounted(() => {
    getEatersList(1)
});

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
            <div class="mb-3">
                <Button @click="state.callModal({modal: 'eater', objectInModal: {}})" color="green">
                    {{ trans('create_new') }}
                </Button>
            </div>
            <div v-if="eaters.length">

            </div>
            <div v-else-if="!loading">
                {{ trans('no_records') }}
            </div>
            <div class="">
                <Paginator :pagination="pagination" />
            </div>
        </div>
    </AppLayout>
</template>
