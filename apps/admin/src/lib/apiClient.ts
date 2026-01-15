const BASE_URL = import.meta.env.VITE_API_URL ?? 'http://localhost:8080';

export const apiClient = async (path: string, init?: RequestInit) => {
  const response = await fetch(`${BASE_URL}${path}`, {
    headers: {
      'Content-Type': 'application/json',
      ...(init?.headers ?? {}),
    },
    ...init,
  });

  if (!response.ok) {
    throw new Error(`API request failed with ${response.status}`);
  }

  return response.json();
};
