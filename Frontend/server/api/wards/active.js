
export default defineEventHandler(async (event) => {
    const authFetch = createAuthFetch(event);
    const config = useRuntimeConfig();
    const uri = `${config.public.API_URL}/api/wards-active`;
    const data = await authFetch(uri);
    
    return data;
});