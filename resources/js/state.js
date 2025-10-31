import { reactive } from 'vue'

const modals= reactive({
    day: {
        zIndex: 3100,
        show: false,
        objectInModal: false,
        modalContentLoaded: false
    },
    dish: {
        zIndex: 3200,
        show: false,
        objectInModal: false,
        modalContentLoaded: false
    },
    ingredient: {
        zIndex: 3300,
        show: false,
        objectInModal: false,
        modalContentLoaded: false
    },
    eater: {
        zIndex: 3400,
        show: false,
        objectInModal: false,
        modalContentLoaded: false
    },
    dietType: {
        zIndex: 3500,
        show: false,
        objectInModal: false,
        savedObject: false,
        modalContentLoaded: false
    },
    mealtime: {
        zIndex: 3600,
        show: false,
        objectInModal: false,
        modalContentLoaded: false
    },
    objectToDelete: {
        zIndex: 5000,
        show: false,
        objectInModal: false,
        modalContentLoaded: false
    }
})

export default {
    modals: modals,
    callModal (data) {
        modals[data.modal]['show'] = true
        modals[data.modal]['objectInModal'] = data.objectInModal
    },
    hideModal (data) {
        modals[data.modal]['show'] = false
        modals[data.modal]['modalContentLoaded'] = false
        modals[data.modal]['objectInModal'] = false
    },
}
