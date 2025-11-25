export default defineEventHandler(async (event) => {
  const authFetch = createAuthFetch(event);
  const body = await readBody(event);
  const config = useRuntimeConfig();
  const uri = `${config.public.API_URL}/api/delete-doctors-order-draft/${body.id}`;

  const response = await authFetch(uri, {
      method: 'DELETE',
  });

  return response;
})
