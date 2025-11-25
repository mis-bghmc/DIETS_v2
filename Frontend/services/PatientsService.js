import { fetchWithHeaders } from './utils/fetchWithHeaders';

export const PatientsService = {
  // Fetch admitted patients
  async getAdmitted() {
    return await fetchWithHeaders(`/api/patients/admitted`);
  },

  // Fetch my patients
  async getMyPatients(id) {
    return await fetchWithHeaders(`/api/patients/doctor/${id}`);
  },

  // Fetch patient data
  async getPatientData(enccode) {
    const formatted_enccode = encodeURIComponent(enccode);
    return await fetchWithHeaders(`/api/patients/data/${formatted_enccode}`);
  },

  // Fetch patient measurements
  async getPatientMeasurements(enccode) {
    const formatted_enccode = encodeURIComponent(enccode);
    return await fetchWithHeaders(`/api/patients/measurements/${formatted_enccode}`);
  },

  // Fetch allergies
  async getAllergies() {
    return await fetchWithHeaders(`/api/patients/allergies`);
  },

  // Fetch precautions
  async getPrecautions() {
    return await fetchWithHeaders(`/api/patients/precautions`);
  },

  // Update food allergies (POST example with body)
  async updateFoodAllergies(data) {
    return fetchWithHeaders(`/api/patients/update-allergies`, {
      method: 'POST',
      body: data.body
    });
  },
};