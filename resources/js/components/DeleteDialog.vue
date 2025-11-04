<script setup lang="ts">
import { trans } from '@/helpers/translator';
import state from '@/state.js'
import Button from '@/components/Button.vue'

function deleteObject() {

}
</script>

<template>
    <div class="py-1">
        <div class="text-center text-gray-800 dark:text-gray-200 text-lg">
            <p>
                {{ trans('are_you_sure_you_want_to_delete_this_record_2') }}
                <span class="font-semibold text-gray-900 dark:text-gray-100">“{{ state.modals.objectToDelete.objectInModal.title }}”</span>?
            </p>
        </div>
        <div class="mt-8 flex justify-center space-x-4 border-t border-gray-200 dark:border-gray-700 pt-4">
            <Button color="red" @click="deleteObject">
                {{ trans('delete') }}
            </Button>
            <Button color="green" @click="state.hideModal({ modal: 'objectToDelete' })">
                {{ trans('cancel') }}
            </Button>
        </div>
    </div>
</template>
