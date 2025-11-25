import { defineStore } from 'pinia';
import { PatientsService } from '~/services/PatientsService';

export const usePrecautionsStore = defineStore('precautions', () => {
    const precautions = ref<any>([]);
    

    //  Fetch precautions
    async function getPrecautions() {
        try {
            precautions.value  = await PatientsService.getPrecautions();

        }catch(error) {
            throw error;
        }
    }


    return { precautions, getPrecautions };
})