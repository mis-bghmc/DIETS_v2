export const AuthService = {

    //  Fetch user details
    async getUserDetails() {

        return await $fetch(`/api/auth/user`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${useAuth().getAuthToken()}`
            },
        });
    },
    
    //  Login
    async login(data) {
        const response = await $fetch(`/api/auth/login`, {
            method: 'POST',
            body: data
        });

        useAuth().setAuthToken(response.token);

        return response;
    },

    //  Logout
    async logout() {
        const response = await $fetch(`/api/auth/logout`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${useAuth().getAuthToken()}`
            },
        });

        useAuth().setAuthToken(null);

        return response;
    },
}