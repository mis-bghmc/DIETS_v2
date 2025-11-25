export default defineEventHandler(async (event) => {
    const authFetch = createAuthFetch(event);
    const config = useRuntimeConfig();
    const body = await readBody(event);
    const uri = `${config.public.API_URL}/api/save-nutrition-assessment`;
    
    const response = await authFetch(uri, {
        method: 'POST',
        body: body
    });
    
    return response;
});