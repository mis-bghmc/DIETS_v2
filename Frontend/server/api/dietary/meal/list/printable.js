export default defineEventHandler(async (event) => {
    const authFetch = createAuthFetch(event);
    const query = getQuery(event);
    const config = useRuntimeConfig();
    const uri = `${config.public.API_URL}/api/meal-list/printable?date=${query.date}&mealtime=${query.mealtime}&ward=${query.ward}`;
    const data = await authFetch(uri);
    
    return data;
});