export default defineEventHandler(async (event) => {
    const config = useRuntimeConfig();
    const body = await readBody(event);
    const uri = `${config.public.API_AUTH}/api/v1/login`;
    
    const response = await $fetch(uri, {
        method: 'POST',
        body: body
    });
    
    return response;
});