export default defineEventHandler(async (event) => {
    const authFetch = createAuthFetch(event);
    const config = useRuntimeConfig();
    const uri = `${config.public.API_URL}/api/patients-admitted`;
    const patients = await authFetch(uri);

    return patients;
});