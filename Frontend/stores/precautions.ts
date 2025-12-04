import { defineStore } from 'pinia';
import { PatientsService } from '~/services/PatientsService';

export const usePrecautionsStore = defineStore('precautions', () => {
    const precautions = ref<any>([]);
    

    //  Fetch precautions
    async function getPrecautions() {
        if(precautions.value.length === 0){
            precautions.value  = await PatientsService.getPrecautions();
            return true;
        }
    }


    return { precautions, getPrecautions };
})