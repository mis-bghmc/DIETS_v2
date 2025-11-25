export default defineEventHandler(async (event) => {
    const authFetch = createAuthFetch(event);
    const config = useRuntimeConfig();
    const { date } = event.context.params; 
    const uri = `${config.public.API_URL}/api/food-requests/${date}`;
    const data = await authFetch(uri);

    return data;
});