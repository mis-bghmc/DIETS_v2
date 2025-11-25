export default defineEventHandler(async (event) => {
    const { date } = event.context.params;
    const config = useRuntimeConfig();
    const uri = `${config.public.API_URL}/api/meal-census/${date}`;
    
    const authFetch = createAuthFetch(event)
    const data = await authFetch(uri);
    return data;
});