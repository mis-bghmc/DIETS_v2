

export default defineEventHandler(async (event) => {
    const authFetch = createAuthFetch(event);
    const { enccode } = event.context.params;
    const config = useRuntimeConfig();
    const uri = `${config.public.API_URL}/api/patient-nutrition-assessment/${enccode}`;
    const nutrition_assessment = await authFetch(uri);
    
    return nutrition_assessment;
});