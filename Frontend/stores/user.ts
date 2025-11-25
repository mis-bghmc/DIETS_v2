import { defineStore } from 'pinia';
import { AuthService } from '~/services/AuthService';

export const useUserStore = defineStore('user-details', () => {
    const user = ref<any>({});

    //  Fetch user details
    async function getUserDetails() {
        try {
            user.value = await AuthService.getUserDetails();

        } catch (error) {
            throw error;
        }
    }

    //  Remove user details
    function removeUserDetails() {
        user.value = {};
    }

    return { user, getUserDetails, removeUserDetails };
})