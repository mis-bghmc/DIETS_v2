import { fetchWithHeaders } from './utils/fetchWithHeaders';

export const EmployeesService = {
    
    //  Fetch employee details
    async getEmployee(id) {
        return await fetchWithHeaders(`/api/employees/nurse/${id}`);
    },

    //  Fetch allowed personnel
    async getAllowedPersonnel() {
        return await fetchWithHeaders(`/api/employees/allowed`);
    },
}