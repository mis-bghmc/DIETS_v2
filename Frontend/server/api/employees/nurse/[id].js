export default defineEventHandler(async (event) => {
    const authFetch = createAuthFetch(event);
    const config = useRuntimeConfig();
    const { id } = event.context.params; 
    const uri = `${config.public.API_URL}/api/employee/${id}`;
    const employee = await authFetch(uri);

    return employee;
});