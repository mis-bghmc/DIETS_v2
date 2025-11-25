export function useAuth() {
    const token = useCookie('auth-token', { maxAge: 60 * 60 * 24 });

    const getAuthToken = () => {
        return token.value;
    }

    const setAuthToken = (_token) => {
        token.value = _token;
    }

    return { getAuthToken, setAuthToken };
  }