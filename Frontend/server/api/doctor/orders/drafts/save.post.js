export default defineEventHandler(async (event) => {
  const authFetch = createAuthFetch(event);
  const body = await readBody(event);
  const config = useRuntimeConfig();
  const uri = `${config.public.API_URL}/api/save-doctors-order-draft`;

  const response = await authFetch(uri, {
      method: 'POST',
      body: body
  });

  return response;
})
