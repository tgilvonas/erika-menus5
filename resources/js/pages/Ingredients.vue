<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Input } from '@/components/ui/input';
import Button from '@/components/Button.vue'
import Paginator from '@/components/Paginator.vue'
import Modal from '@/components/Modal.vue'
import DeleteDialog from '@/components/DeleteDialog.vue'
import Ingredient from "@/components/forms/Ingredient.vue";
import FlashMessage from "@/components/FlashMessage.vue";
import { trans } from '@/helpers/translator';
import {ref, onMounted, onBeforeUnmount} from "vue";
import axios from "axios";
import { route } from 'ziggy-js';
import emitter from '@/eventBus.js'
import state from '@/state.js'

const ingredients = ref([])
const pagination = ref([])
const searchText = ref('')
const loading = ref(false)

onMounted(() => {
    getIngredientsList(1)
    emitter.on('paginatorClicked', handlePaginatorClick)
    emitter.on('ingredientSaved', handleListChanged)
    emitter.on('objectDeleted', handleListChanged)
})
onBeforeUnmount(() => {
    emitter.off('paginatorClicked', handlePaginatorClick)
    emitter.off('ingredientSaved', handleListChanged)
    emitter.off('objectDeleted', handleListChanged)
})

function handlePaginatorClick(payload: object) {
    getIngredientsList(payload.page)
}

function handleListChanged() {
    getIngredientsList(1)
}

function getIngredientsList(page: number) {
    loading.value = true;
    axios.get(route('ingredients.json_list'), {
        params: {
            order_by_field: 'title',
            order_by_direction: 'asc',
            page: page,
            search_text: searchText.value,
            paginate_by: 10
        }
    }).then(function(response){
        loading.value = false
        ingredients.value = response.data.data
        pagination.value = response.data.links
    })
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: trans('ingredients'),
        href: dashboard().url,
    },
];
</script>

<template>
    <Head :title="trans('ingredients')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <FlashMessage type="success"></FlashMessage>
            <FlashMessage type="error"></FlashMessage>
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
            <Modal modal-name="objectToDelete">
                <template #modal_title>
                    {{ trans('delete_record') }}
                </template>
                <template #content>
                    <div>
                        <DeleteDialog :delete-url="route('ingredients.delete').toString()" />
                    </div>
                </template>
            </Modal>
            <p class="mb-3">{{ trans('ingredients_list_description') }}</p>
            <div class="flex items-center space-x-2 pb-3">
                <label for="search" class="">
                    {{ trans('ingredient')}}:
                </label>
                <Input
                    class="block w-full"
                    v-model="searchText"
                    :placeholder="trans('enter_text_for_search')"
                    @keyup="getIngredientsList"
                />
            </div>
            <div class="mb-3">
                <Button @click="state.callModal({modal: 'ingredient', objectInModal: {}})" color="green">
                    {{ trans('create_new') }}
                </Button>
            </div>
            <!--
            <div class="text-center" v-if="loading">
                <img class="mx-auto" src="/images/loader.svg" alt="{{ trans('loading') }}" />
            </div>
            -->
            <table class="table-auto border-collapse w-full" v-if="ingredients.length">
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
                    <tr v-for="(ingredient, key) in ingredients" v-bind:key="key">
                        <td class="border p-2">{{ ingredient.title }}</td>
                        <td class="border p-2 text-right">{{ ingredient.proteins.toFixed(3) }}</td>
                        <td class="border p-2 text-right">{{ ingredient.fat.toFixed(3) }}</td>
                        <td class="border p-2 text-right">{{ ingredient.carbohydrates.toFixed(3) }}</td>
                        <td class="border p-2 text-right">{{ ingredient.calories.toFixed(3) }}</td>
                        <td class="border p-2">
                            <Button @click="state.callModal({modal: 'ingredient', objectInModal: ingredient})">
                                {{ trans('edit') }}
                            </Button>
                            <Button @click="state.callModal({modal: 'objectToDelete', objectInModal: ingredient})" color="red">
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
