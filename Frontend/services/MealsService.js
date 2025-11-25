import { fetchWithHeaders } from './utils/fetchWithHeaders';

export const MealsService = {
    
    //  Fetch meal census by date
    async getMealCensus(date) {
        return await fetchWithHeaders(`/api/dietary/meal/census/${date}`);
    },

    //  Fetch diet list for printing
    async getDietListPrintable(date, mealtime, ward) {
        return await fetchWithHeaders(`/api/dietary/meal/list/printable?date=${date}&mealtime=${mealtime}&ward=${ward}`);
    },

    //  Fetch latest diet list
    async getDietListLatest() {
        return await fetchWithHeaders(`/api/dietary/meal/list/latest`);
    },

    //  Fetch diet tags
    async getDietTags(group, option, wards) {
        return await fetchWithHeaders(`/api/dietary/meal/list/tags?group=${group}&option=${option}&wards=${wards}`);
    },

    //  Fetch diet history
    async getDietHistory(enccode) {
        return await fetchWithHeaders(`/api/dietary/meal/history/${enccode}`);
    },

    //  Update meal status
    async updateMealStatus(data) {
        return fetchWithHeaders(`/api/dietary/diet/update-meal-status`, {
            method: 'PUT',
            headers: data.headers,
            body: data.body
        });
    },
}