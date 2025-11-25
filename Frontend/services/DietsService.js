import { fetchWithHeaders } from './utils/fetchWithHeaders';

export const DietsService = {
    //  Fetch diets
    async getDiets() {
        return fetchWithHeaders(`/api/diets/active`);
    },

    //  Fetch enteral feeding modes
    async getFeedingModes() {
        return fetchWithHeaders(`/api/diets/feeding-modes`);
    },
}