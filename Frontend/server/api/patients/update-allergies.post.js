export default defineEventHandler(async (event) => {
  const authFetch = createAuthFetch(event);
  const body = await readBody(event);
  const config = useRuntimeConfig();
  const uri = `${config.public.API_URL}/api/update-patient-food-allergies`;

  const response = await authFetch(uri, {
      method: 'POST',
      body: body
  });

  return response;
})
