export default defineEventHandler(async (event) => {
    const config = useRuntimeConfig();
    const uri = `${config.public.API_URL}/api/ping`;
    const message = await $fetch(uri);

    return message;
});