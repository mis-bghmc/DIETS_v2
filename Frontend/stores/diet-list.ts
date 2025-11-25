import { defineStore } from 'pinia';
import { MealsService } from '~/services/MealsService';

export const useDietListStore = defineStore('diet-list', () => {
    const s_diet_list = ref<any>({});

    //  Fetch diet list
    async function getDietListLatest() {
        s_diet_list.value  = await MealsService.getDietListLatest();
        return true;
    }

    //  Update meal status
    function updateMealStatus(hpercode: string, meal_time: string, status: string) {
        const groups = ['O', 'E', 'ONS'];

        for(const g of groups) {
            const patient = s_diet_list.value[g].find((p: {hpercode: string, meal_time: string}) => p.hpercode === hpercode && p.meal_time === meal_time);
            
            if(patient) {
                patient.meal_status = status;
                break;
            }
        }
    }

    //  Update patient field
    function updateField(hpercode: string, new_value: string, field: string) {
        const groups = ['O', 'E', 'ONS'];

        for(const g of groups) {
            const patients = s_diet_list.value[g].filter((p: {hpercode: string}) => p.hpercode === hpercode);
            
            patients.forEach((patient: any) => {
                patient[field] = new_value;
            });
        }
    }

    return { s_diet_list, getDietListLatest, updateMealStatus, updateField };
})