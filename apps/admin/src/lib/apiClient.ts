const API_URL = import.meta.env.VITE_API_URL ?? 'http://localhost:8080'

type RequestOptions = RequestInit & { path: string }

export async function apiRequest<T>({ path, ...options }: RequestOptions): Promise<T> {
  const url = `${API_URL}${path}`
  const response = await fetch(url, {
    headers: {
      'Content-Type': 'application/json',
      ...(options.headers ?? {}),
    },
    ...options,
  })

  if (!response.ok) {
    const errorBody = await response.text()
    const suffix = errorBody ? `: ${errorBody}` : ''
    throw new Error(`API request to ${url} failed with status ${response.status}${suffix}`)
  }

  return (await response.json()) as T
}

export async function getHealth(): Promise<{ status: string }> {
  return apiRequest<{ status: string }>({ path: '/health', method: 'GET' })
}
