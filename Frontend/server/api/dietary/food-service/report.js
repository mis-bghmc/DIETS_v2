export default defineEventHandler(async (event) => {
    const authFetch = createAuthFetch(event);
    const query = getQuery(event);
    const config = useRuntimeConfig();
    const uri = `${config.public.API_URL}/api/food-service-history?date=${query.date}&ward=${query.ward}&meal_time=${query.mealtime}`;
    const data = await authFetch(uri);
    
    return data;
});