export default defineEventHandler(async (event) => {
    const authFetch = createAuthFetch(event);
    const config = useRuntimeConfig();
    const body = await readBody(event);
    const header = getHeader(event, 'X-Socket-Id');
    const uri = `${config.public.API_URL}/api/food-requests/create`;

    const response = await authFetch(uri, {
        method: 'POST',
        headers: {
            'X-Socket-Id': header
        },
        body: body
    });
    
    return response;
});