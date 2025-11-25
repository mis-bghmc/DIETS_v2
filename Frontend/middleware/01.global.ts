import { defineNuxtRouteMiddleware, navigateTo } from "nuxt/app";

const allowed_users: Record<string, Array<string>> = {
    '59': ['/dietary', '/doctors'],
    '60': ['/dietary/diet-list']
}

const excluded_paths = ['/auth/emr_tunnel', '/auth/login'];

export default defineNuxtRouteMiddleware(async (to) => {
    const user_store = useUserStore();
    const { user } = storeToRefs(user_store);

    //  Ignore if user doesn't have auth token and is accessing login page
    if (!useAuth().getAuthToken() && to.path.startsWith('/auth/login')) return;

    //  Ignore if user is accessing EMR tunnel
    if (to.path.startsWith('/auth/emr_tunnel')) return;

    try {
        //  Check if user is authenticated
        if (!Object.keys(user.value)?.length) await user_store.getUserDetails();

        const path = allowed_users[user.value?.user_level] || ['/doctors'];

        //  Check if the user is trying to access a path that is not allowed for their user_level
        if (!path?.some(item => to.path.startsWith(item))) return navigateTo(path[0]);

    } catch (error: any) {
        if (!excluded_paths.includes(to.path)) return navigateTo(`/auth/login?status=${error.statusCode}`);
    }
});