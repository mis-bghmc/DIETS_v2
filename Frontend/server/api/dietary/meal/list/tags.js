export default defineEventHandler(async (event) => {
    const authFetch = createAuthFetch(event);
    const query = getQuery(event);
    const config = useRuntimeConfig();
    const uri = `${config.public.API_URL}/api/meal-tags?group=${query.group}&option=${query.option}&wards=${query.wards}`;
    const data = await authFetch(uri);
    
    return data;
});