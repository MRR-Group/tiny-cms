const API_URL = import.meta.env.VITE_API_URL ?? 'http://localhost:8080'

type RequestOptions = RequestInit & { path: string }

export async function apiRequest<T>({ path, ...options }: RequestOptions): Promise<T> {
  const response = await fetch(`${API_URL}${path}`, {
    headers: {
      'Content-Type': 'application/json',
      ...(options.headers ?? {}),
    },
    ...options,
  })

  if (!response.ok) {
    throw new Error(`Request failed with status ${response.status}`)
  }

  return (await response.json()) as T
}

export async function getHealth(): Promise<{ status: string }> {
  return apiRequest<{ status: string }>({ path: '/health', method: 'GET' })
}
