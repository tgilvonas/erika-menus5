<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import {trans} from "@/helpers/translator";
import Button from '@/components/Button.vue';
import {ref} from "vue";
import state from "@/state";
import Modal from '@/components/Modal.vue';
import FlashMessage from "@/components/FlashMessage.vue";

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
</script>

<template>
    <Head :title="trans('dishes')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <FlashMessage type="success"></FlashMessage>
            <FlashMessage type="error"></FlashMessage>
            <Modal modal-name="dish">
                <template #modal_title>
                    {{ trans('dish') }}
                </template>
            </Modal>
            <div class="mb-3">
                <Button @click="state.callModal({modal: 'dish', objectInModal: {}})" color="green">
                    {{ trans('create_new') }}
                </Button>
            </div>
            <div v-if="dishes.length">

            </div>
            <div v-else-if="!loading">
                {{ trans('no_records') }}
            </div>
        </div>
    </AppLayout>
</template>
