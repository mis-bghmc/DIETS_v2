import { fetchWithHeaders } from './utils/fetchWithHeaders';

export const NotificationsService = {
    
    //  Fetch notifications
    async getNotifications(date_range) {
        return await fetchWithHeaders(`/api/notifications/range/${date_range}`);
    },

    //  Update notification seen date for priority diets
    async acknowledge(data) {
        return fetchWithHeaders(`/api/notifications/acknowledge`, {
            method: 'PUT',
            headers: data.headers,
            body: data.body
        });
    },

    //  Update notification seen date for regular diets
    async acceptLateUpdate(data) {
        return fetchWithHeaders(`/api/notifications/accept-late-update`, {
            method: 'PUT',
            headers: data.headers,
            body: data.body
        });
    },
}