export const fetchWithHeaders = async (url, options = {}) => {
    const headers = import.meta.server ? useRequestHeaders(['cookie']) : {};
    return await $fetch(url, {
            ...options,
            headers: {
            ...headers,
            ...options.headers // Merge with any custom headers passed in options
        }
    });
};
