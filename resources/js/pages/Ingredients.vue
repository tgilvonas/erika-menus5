<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { Input } from '@/components/ui/input';
import { trans } from '@/helpers/translator';
import {ref, onMounted} from "vue";
import axios from "axios";
import { route } from 'ziggy-js';

let ingredients = ref([])
let searchText = ref('')
let loading = ref(false)

onMounted(() => {
    getIngredientsList()
})

function getIngredientsList() {
    loading.value = true;
    axios.get(route('ingredients.json_list'), {
        params: {
            order_by_field: 'title',
            order_by_direction: 'asc',
            page: 1,
            search_text: searchText.value,
            paginate_by: 10
        }
    }).then(function(response){
        loading.value = false;
        ingredients.value = response.data.data;
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
        <div
            class="p-4"
        >
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
                <button class="">{{ trans('create_new') }}</button>
            </div>
            <table class="table-auto border-collapse w-full" v-if="ingredients.length">
                <thead>
                    <tr>
                        <th class="border p-1">{{ trans('title') }}</th>
                        <th class="border p-1">{{ trans('proteins') }}</th>
                        <th class="border p-1">{{ trans('fat') }}</th>
                        <th class="border p-1">{{ trans('carbohydrates') }}</th>
                        <th class="border p-1">{{ trans('kilocalories') }}</th>
                        <th class="border p-1"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="ingredient in ingredients">
                        <td class="border p-1">{{ ingredient.title }}</td>
                        <td class="border p-1 text-right">{{ ingredient.proteins.toFixed(3) }}</td>
                        <td class="border p-1 text-right">{{ ingredient.fat.toFixed(3) }}</td>
                        <td class="border p-1 text-right">{{ ingredient.carbohydrates.toFixed(3) }}</td>
                        <td class="border p-1 text-right">{{ ingredient.calories.toFixed(3) }}</td>
                        <td class="border p-1">
                        </td>
                    </tr>
                </tbody>
            </table>
            <div v-else>
                {{ trans('no_records') }}
            </div>
        </div>
    </AppLayout>
</template>
