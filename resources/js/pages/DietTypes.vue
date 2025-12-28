<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { trans } from '@/helpers/translator';
import {ref, onMounted, onBeforeUnmount} from "vue";
import axios from "axios";
import {route} from "ziggy-js";
import state from "@/state";
import Button from '@/components/Button.vue'

const dietTypes = ref([])
const pagination = ref([])
const searchText = ref('')
const loading = ref(false)

onMounted(() => {
    getDietTypesList(1)
})
onBeforeUnmount(() => {

})

function handlePaginatorClick(payload: object) {

}

function handleListChanged() {

}

function getDietTypesList(page: number) {
    loading.value = true;
    axios.get(route('diet_types.json_list'), {
        params: {
            order_by_field: 'diet_type',
            order_by_direction: 'asc',
            page: page,
            search_text: searchText.value,
            paginate_by: 10
        }
    }).then(function(response){
        loading.value = false
        dietTypes.value = response.data.data
        pagination.value = response.data.links
    })
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: trans('diets'),
        href: dashboard().url,
    },
];
</script>

<template>
    <Head :title="trans('diets')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="p-4"
        >
            <div class="mb-3">
                <Button @click="state.callModal({modal: 'dietType', objectInModal: {}})" color="green">
                  {{ trans('create_new') }}
                </Button>
            </div>
            <table class="table-auto border-collapse w-full" v-if="dietTypes.length">
                <thead>
                <tr>
                    <th class="border p-2">{{ trans('diet') }}</th>
                    <th class="border p-2">{{ trans('proteins') }}</th>
                    <th class="border p-2">{{ trans('fat') }}</th>
                    <th class="border p-2">{{ trans('carbohydrates') }}</th>
                    <th class="border p-2">{{ trans('kilocalories') }}</th>
                    <th class="border p-2"></th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(dietType, key) in dietTypes" v-bind:key="key">
                    <td class="border p-2">{{ dietType.title }}</td>
                    <td class="border p-2 text-right">{{ dietType.proteins_min.toFixed(2) }} - {{ dietType.proteins_max.toFixed(2) }}</td>
                    <td class="border p-2 text-right">{{ dietType.fat_min.toFixed(2) }} - {{ dietType.fat_max.toFixed(2) }}</td>
                    <td class="border p-2 text-right">{{ dietType.carbohydrates_min.toFixed(2) }} - {{ dietType.carbohydrates_max.toFixed(2) }}</td>
                    <td class="border p-2 text-right">{{ dietType.calories_min.toFixed(2) }} - {{ dietType.calories_max.toFixed(2) }}</td>
                    <td class="border p-2">
                        <Button @click="state.callModal({modal: 'dietType', objectInModal: dietType})">
                            {{ trans('edit') }}
                        </Button>
                        <Button @click="state.callModal({modal: 'objectToDelete', objectInModal: dietType})" color="red">
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
