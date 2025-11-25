import { fetchWithHeaders } from './utils/fetchWithHeaders';

export const WardsService = {
    //  Fetch wards
    async getWards() {
        return fetchWithHeaders(`/api/wards/active`);
    },
}