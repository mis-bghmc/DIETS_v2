import { fetchWithHeaders } from './utils/fetchWithHeaders';

export const FoodService = {
    //  Fetch food service monitoring report
    async getReport(date, ward, mealtime) {
        return fetchWithHeaders(`/api/dietary/food-service/report?date=${date}&ward=${ward}&mealtime=${mealtime}`);
    },

    //  Verify food service report
    async verify(data) {
        return fetchWithHeaders(`/api/dietary/food-service/verify`, {
            method: 'POST',
            headers: data.headers,
            body: data.body
        });
    },
}