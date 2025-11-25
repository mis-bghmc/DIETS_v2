import { fetchWithHeaders } from './utils/fetchWithHeaders';

export const FoodRequestsService = {
    //  Fetch food service monitoring report
    async getRequests(date) {
        return fetchWithHeaders(`/api/dietary/food-requests/${date}`);
    },

    //  Create new food request
    async create(data) {
        return fetchWithHeaders(`/api/dietary/food-requests/create`, {
            method: 'POST',
            headers: data.headers,
            body: data.body
        });
    },

    //  Update food request
    async update(data) {
        return fetchWithHeaders(`/api/dietary/food-requests/update`, {
            method: 'PUT',
            headers: data.headers,
            body: data.body
        });
    },
}