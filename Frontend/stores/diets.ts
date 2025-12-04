import { defineStore } from 'pinia';
import { DietsService } from '~/services/DietsService';

export const useDietsStore = defineStore('diets', () => {
    const diets = ref<any>([]);
    const diets_routine = ref<any>([]);
    const diets_routine_non_breastfeeding = ref<any>([]);
    const diets_therapeutic = ref<any>([]);
    const diets_soft = ref<any>([]);
    const diets_enteral = ref<any>([]);
    const sns_enteral = ref<any>([]);
    const feeding_modes = ref<any>([]);
    

    //  Fetch diets
    async function getDiets() {
        if(diets.value.length === 0){
            //  All
            diets.value  = await DietsService.getDiets();
    
            //  Routine
            diets_routine.value = diets.value?.filter((item: {diettype: string}) => item.diettype === 'RT');
            diets_routine.value?.push({dietcode: 'Therapeutic Diets', dietname: 'Therapeutic Diets'});
    
            //  Routine for patients <= 6 months old
            diets_routine_non_breastfeeding.value = diets_routine.value?.filter((item: {dietcode: string}) =>  item.dietcode !== '42');
    
            //  Therapeutic
            diets_therapeutic.value = diets.value?.filter((item: {diettype: string}) => item.diettype === 'TP');
    
            //  Soft
            const diets_soft_prefixed = diets.value?.filter((item: {diettype: string}) => item.diettype === 'SD');
            diets_soft.value = diets_soft_prefixed?.map((item: {dietname: string}) => ({...item, dietname: item.dietname?.replace(/^Soft /, '')}));
    
            //  Enteral
            const enteral = diets.value?.filter((item: {diettype: string}) => item.diettype === 'EN');
    
            //  Enteral - diet
            diets_enteral.value = enteral?.filter((item: {dietcode: string}) =>  item.dietcode !== '44');
    
            //  Enteral - SNS
            sns_enteral.value = enteral?.filter((item: {dietcode: string}) =>  item.dietcode !== '34');

            return true;
        }
    }

    //  Fetch enteral feeding modes
    async function getFeedingModes() {
        if(feeding_modes.value.length === 0){
            feeding_modes.value = await DietsService.getFeedingModes();
            return true;
        }
    }


    return { diets, diets_routine, diets_routine_non_breastfeeding, diets_therapeutic, diets_soft, diets_enteral, sns_enteral, feeding_modes, getDiets, getFeedingModes };
})