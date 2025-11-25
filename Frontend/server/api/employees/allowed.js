export default defineEventHandler(async (event) => {
    const authFetch = createAuthFetch(event);
    const config = useRuntimeConfig();
    const uri = `${config.public.API_URL}/api/employees/allowed-personnel`;
    const employees = await authFetch(uri);

    return employees;
});