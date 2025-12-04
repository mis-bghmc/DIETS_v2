import { defineStore } from 'pinia';
import { EmployeesService } from '~/services/EmployeesService';

export const useEmployeesStore = defineStore('employees', () => {
    const allowed_personnel = ref<any>([]);

    //  Fetch wards
    async function getAllowedPersonnel() {
        if(allowed_personnel.value.length === 0){
            allowed_personnel.value  = await EmployeesService.getAllowedPersonnel();
            return true;
        }
    }


    return { allowed_personnel, getAllowedPersonnel };
})