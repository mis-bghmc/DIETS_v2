export default defineEventHandler(async (event) => {
    const authFetch = createAuthFetch(event);
    const config = useRuntimeConfig();
    const { date_range } = event.context.params; 
    const uri = `${config.public.API_URL}/api/notifications/${date_range}`;
    const response = await authFetch(uri);
    
    return response;
});