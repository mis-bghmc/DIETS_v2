export function useThemeCookie() {
    const save = (name, data) => {
        const cookie = useCookie(name, { maxAge: 60 * 60 * 24 * 365 });
        cookie.value = JSON.stringify(data);
    }

    const load = (name) => {
        const cookie = useCookie(name);
        return cookie.value;
    }

    const clear = () => {
        const cookie = useCookie(name);
        cookie.value = null;
    }

    return { save, load, clear };
}