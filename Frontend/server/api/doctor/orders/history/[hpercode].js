
export default defineEventHandler(async (event) => {
    const authFetch = createAuthFetch(event);
    const config = useRuntimeConfig();
    const { hpercode } = event.context.params;
    const uri = `${config.public.API_URL}/api/doctors-orders-history/${hpercode}`;
    const data = await authFetch(uri);
    
    return data;
});