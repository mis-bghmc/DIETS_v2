import { fetchWithHeaders } from './utils/fetchWithHeaders';

export const DoctorsOrdersService = {
    
    //  Fetch all admitted patients doctor's orders
    async getDoctorsOrders() {
        return fetchWithHeaders(`/api/doctor/orders/all`);
    },

    //  Fetch total number of doctor's orders
    async getDoctorsOrdersTotal() {
        return fetchWithHeaders(`/api/doctor/orders/total`);
    },

    //  Fetch doctor's orders history
    async getHistory(hpercode) {
        return fetchWithHeaders(`/api/doctor/orders/history/${hpercode}`);
    },

    //  Fetch doctor's drafts
    async getDrafts(id) {
        return fetchWithHeaders(`/api/doctor/orders/drafts/${id}`);
    },

    //  Update precautions
    async updatePrecautions(data) {
        return fetchWithHeaders(`/api/doctor/orders/update-precautions`, {
            method: 'POST',
            body: data.body
        });
    },

    //  Save order
    async saveOrder(data) {
        return fetchWithHeaders(`/api/doctor/orders/save`, {
            method: 'POST',
            body: {
                hpercode: data.body.patient.hpercode,
                enccode: data.body.patient.enccode,
                entryBy: data.body.entry_by,
                licno: data.body.entry_by,

                dietCategory: data.body.order.category,
                dietCode1: data.body.order.diet_type,
                dietName: data.body.order.diet_name,
                remarks: data.body.order.remarks,

                calories: data.body.order.calories,
                protein: data.body.order.protein,
                fiber: data.body.order.fiber,
                carbohydrates: data.body.order.carbohydrates,
                fats: data.body.order.fats,
                dilution: data.body.order.dilution,

                allergies: data.body.order.food_allergies,
                precautions: data.body.order.precautions,

                onsType: data.body.order.sns_type?.[0] || null,
                onsType2: data.body.order.sns_type?.[1] || null,
                ons: data.body.order.sns_name?.[0] || null,
                ons2: data.body.order.sns_name?.[1] || null,
                onsFrequency: data.body.order.sns_frequency,
                onsDescription: data.body.order.sns_description,

                feedingMode: data.body.order.feeding_mode,
                feedingDuration: data.body.order.feeding_duration,
                feedingFrequency: data.body.order.feeding_frequency,

                reproductiveStatus: data.body.order.maternal_status,

                previousDiet: data.body.patient.dietcode,
            }
        });
    },

    //  Save order draft
    async saveDraft(data) {
        return fetchWithHeaders(`/api/doctor/orders/drafts/save`, {
            method: 'POST',
            body: data.body
        });
    },

    //  Delete draft
    async deleteDraft(data) {
        return fetchWithHeaders(`/api/doctor/orders/drafts/delete`, {
            method: 'DELETE',
            body: data.body
        });
    },
}