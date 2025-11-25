export default defineEventHandler(async (event) => {
    const authFetch = createAuthFetch(event)
    const { enccode } = event.context.params;
    const config = useRuntimeConfig();
    const uri = `${config.public.API_URL}/api/meal-history/${enccode}`;
    const diet_history = await authFetch(uri);

    return diet_history;
});