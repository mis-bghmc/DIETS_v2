import { defineStore } from 'pinia';
import { WardsService } from '~/services/WardsService';

export const useWardsStore = defineStore('wards', () => {
    const wards = ref<any>([]);

    //  Fetch wards
    async function getWards() {
        try {
            wards.value  = await WardsService.getWards();
            wards.value.push({wardcode: 'ERB', wardname: 'ER Boarders'});
            useSort().sortArray(wards.value, 'wardname');

        }catch(error) {
            throw error;
        }
    }


    return { wards, getWards };
})