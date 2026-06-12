<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import {trans} from "@/helpers/translator";
import Button from '@/components/Button.vue';
import {ref, onMounted, onBeforeUnmount} from "vue";
import state from "@/state";
import Modal from '@/components/Modal.vue';
import FlashMessage from "@/components/FlashMessage.vue";
import Dish from "@/components/forms/Dish.vue";
import Ingredient from "@/components/forms/Ingredient.vue";
import emitter from '@/eventBus.js';
import axios from "axios";
import {route} from "ziggy-js";

const dishes = ref([]);
const pagination = ref([]);
const searchText = ref('');
const loading = ref(false);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: trans('dishes'),
        href: dashboard().url,
    },
];

onMounted(() => {
    getDishesList(1);
    emitter.on('paginatorClicked', handlePaginatorClick);
    emitter.on('dishSaved', handleListChanged);
    emitter.on('objectDeleted', handleListChanged);
});
onBeforeUnmount(() => {
    emitter.off('paginatorClicked', handlePaginatorClick);
    emitter.off('dishSaved', handleListChanged);
    emitter.off('objectDeleted', handleListChanged);
});

function handlePaginatorClick(payload: object) {
    getDishesList(payload.page);
}

function handleListChanged() {
    getDishesList(1);
}

//@TODO: implement
function getDishesList(page) {
    loading.value = true;
    axios.get(route('dishes.json_list'), {
        params: {
            page: page,
            search_text: searchText.value,
            paginate_by: 10
        }
    }).then(function(response){
        loading.value = false;
        dishes.value = response.data.data;
        pagination.value = response.data.links;
    });
}

</script>

<template>
    <Head :title="trans('dishes')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <FlashMessage type="success"></FlashMessage>
            <FlashMessage type="error"></FlashMessage>
            <Modal modal-name="dish" size="lg">
                <template #modal_title>
                    {{ trans('dish') }}
                </template>
                <template #content>
                    <div>
                        <Dish :dish="state.modals.dish.objectInModal" />
                    </div>
                </template>
            </Modal>
            <Modal modal-name="ingredient">
                <template #modal_title>
                    {{ trans('ingredient') }}
                </template>
                <template #content>
                    <div>
                        <Ingredient :ingredient="state.modals.ingredient.objectInModal" />
                    </div>
                </template>
            </Modal>
            <div class="mb-3">
                <Button @click="state.callModal({modal: 'dish', objectInModal: {}})" color="green">
                    {{ trans('create_new') }}
                </Button>
            </div>
            <table class="table-auto border-collapse w-full" v-if="dishes.length">
                <thead>
                <tr>
                    <th class="border p-2">{{ trans('title') }}</th>
                    <th class="border p-2">{{ trans('proteins') }}</th>
                    <th class="border p-2">{{ trans('fat') }}</th>
                    <th class="border p-2">{{ trans('carbohydrates') }}</th>
                    <th class="border p-2">{{ trans('kilocalories') }}</th>
                    <th class="border p-2"></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(dish, key) in dishes" v-bind:key="key">
                    <td class="border p-2">{{ dish.title }}</td>
                    <td class="border p-2 text-right">{{ dish.proteins_total.toFixed(3) }}</td>
                    <td class="border p-2 text-right">{{ dish.fat_total.toFixed(3) }}</td>
                    <td class="border p-2 text-right">{{ dish.carbohydrates_total.toFixed(3) }}</td>
                    <td class="border p-2 text-right">{{ dish.calories_total.toFixed(3) }}</td>
                    <td class="border p-2">
                        <Button @click="state.callModal({modal: 'dish', objectInModal: dish})">
                            {{ trans('edit') }}
                        </Button>
                        <Button @click="state.callModal({modal: 'objectToDelete', objectInModal: dish})" color="red">
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
