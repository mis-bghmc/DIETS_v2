import { getCookie } from 'h3';

export const createAuthFetch = (event) => (url, options = {}) => {
  const authToken = getCookie(event, 'auth-token') || '';

  return $fetch(url, {
    ...options,
    headers: {
      ...options.headers,
      Authorization: `Bearer ${authToken}`,
    },
  });
};
