import { defineStore } from 'pinia';
import { EmployeesService } from '~/services/EmployeesService';

export const useEmployeesStore = defineStore('wards', () => {
    const allowed_personnel = ref<any>([]);

    //  Fetch wards
    async function getAllowedPersonnel() {
        try {
            allowed_personnel.value  = await EmployeesService.getAllowedPersonnel();

        }catch(error) {
            throw error;
        }
    }


    return { allowed_personnel, getAllowedPersonnel };
})