
export default defineEventHandler(async (event) => {
    const authFetch = createAuthFetch(event);
    const config = useRuntimeConfig();
    const uri = `${config.public.API_URL}/api/precautions`;
    const data = await authFetch(uri);
    
    return data;
});