export default defineEventHandler(async (event) => {
    const config = useRuntimeConfig();
    const header = getHeader(event, 'Authorization');
    const uri = `${config.public.API_AUTH}/api/v1/logout`;
    
    const response = await $fetch(uri, {
        method: 'POST',
        headers: {
            'Authorization': header,
            'Accept': 'application/json'
        },
    });
    
    return response;
});