export default defineEventHandler(async (event) => {
    const config = useRuntimeConfig();
    const header = getHeader(event, 'Authorization');
    const uri = `${config.public.API_AUTH}/api/v1/user`;
    
    const response = await $fetch(uri, {
        method: 'GET',
        headers: {
            'Authorization': header,
            'Accept': 'application/json'
        },
    });
    
    return response;
});