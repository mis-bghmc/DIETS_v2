import { defineStore } from 'pinia';
import { PatientsService } from '~/services/PatientsService';

export const useAllergiesStore = defineStore('allergies', () => {
    const allergies = ref<any>([]);
    const main = ref<any>([]);
    const sub = ref<any>([]);
    

    //  Fetch allergies
    async function getAllergies() {
        try {
            //  All
            allergies.value  = await PatientsService.getAllergies();

            //  Main
            main.value = allergies.value?.filter((item: ({category: string})) => item.category === 'main');

            //  Sub
            sub.value = allergies.value?.filter((item: ({category: string})) => item.category === 'sub');

        }catch(error) {
            throw error;
        }
    }


    return { allergies, main, sub, getAllergies };
})