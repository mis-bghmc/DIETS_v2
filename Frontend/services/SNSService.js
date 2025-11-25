import { fetchWithHeaders } from './utils/fetchWithHeaders';

export const SNSService = {
    
    //  Update meal status
    async updateMealStatus(data) {
        return fetchWithHeaders(`/api/dietary/sns/update-meal-status`, {
            method: 'PUT',
            headers: data.headers,
            body: data.body
        });
    }
}