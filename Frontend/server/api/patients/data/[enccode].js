export default defineEventHandler(async (event) => {
    const authFetch = createAuthFetch(event);
    const config = useRuntimeConfig();
    const { enccode } = event.context.params; 
    const decoded = decodeURIComponent(enccode);
    const uri = `${config.public.API_URL}/api/patient/${decoded}`;
    const data = await authFetch(uri);

    return data;
})
