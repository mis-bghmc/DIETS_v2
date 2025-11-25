
export default defineEventHandler(async (event) => {
    const authFetch = createAuthFetch(event);
    const config = useRuntimeConfig();
    const uri = `${config.public.API_URL}/api/diet-types-enteral-feeding-modes`;
    const data = await authFetch(uri);
    
    return data;
});