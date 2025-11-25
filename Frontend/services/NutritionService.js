import { fetchWithHeaders } from './utils/fetchWithHeaders';

export const NutritionService = {
    
    //  Fetch screening history
    async getScreeningHistory(enccode) {
        const formatted_enccode = encodeURIComponent(enccode);

        return await fetchWithHeaders(`/api/nutrition/screening/${formatted_enccode}`);
    },

    //  Fetch assessment history
    async getAssessmentHistory(enccode) {
        const formatted_enccode = encodeURIComponent(enccode);

        return await fetchWithHeaders(`/api/nutrition/assessment/${formatted_enccode}`);
    },

    //  Save assessment
    async saveScreening(data) {
        return fetchWithHeaders(`/api/nutrition/screening/save`, {
            method: 'POST',
            body: data.body
        });
    },

    //  Save assessment
    async saveAssessment(data) {
        return fetchWithHeaders(`/api/nutrition/assessment/save`, {
            method: 'POST',
            body: data.body
        });
    },
}