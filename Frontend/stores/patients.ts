import { defineStore } from 'pinia';
import { PatientsService } from '~/services/PatientsService';

export const usePatientsStore = defineStore('patients', () => {
    const admitted_patients = ref<any>([]);
    const my_admitted_patients = ref<any>([]);

    //  Fetch all admitted patients
    async function getAdmittedPatients() {
        admitted_patients.value  = await PatientsService.getAdmitted();
        return true;
    }

    //  Fetch admitted patients of a specific doctor
    async function getMyPatients(id: string) {
        my_admitted_patients.value  = await PatientsService.getMyPatients(id);
        return true;
    }


    return { admitted_patients, my_admitted_patients, getAdmittedPatients, getMyPatients };
})