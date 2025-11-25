export default defineEventHandler(async (event) => {
    const config = useRuntimeConfig();
    
    const params = getQuery(event);
        
    const uri = `${config.public.API_URL}/api/userDetails/${params.empid}`;

    const response = await $fetch(uri, {
        method: 'GET'
    });

    return response
});