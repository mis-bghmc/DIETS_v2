export default defineEventHandler(async (event) => {
    const authFetch = createAuthFetch(event);
    const config = useRuntimeConfig();
    const { enccode } = event.context.params;
    const uri = `${config.public.API_URL}/api/patient-nutrition/${enccode}`;
    const nutrition = await authFetch(uri);
    
    return nutrition;
});