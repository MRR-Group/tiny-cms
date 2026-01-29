import { User } from '../site/types';

export class UserService {
  constructor(private readonly baseUrl: string) {}

  private async request<T>(endpoint: string, options: RequestInit): Promise<T> {
    const token = localStorage.getItem('authToken');

    const headers: HeadersInit = {
      'Content-Type': 'application/json',
    };

    if (token) {
      Object.assign(headers, { Authorization: `Bearer ${token}` });
    }

    const response = await fetch(`${this.baseUrl}${endpoint}`, {
      ...options,
      headers,
    });

    if (!response.ok) {
      const error = await response.json().catch(() => ({
        error: { message: 'An error occurred' },
      }));
      throw new Error(error.error?.message || error.error || 'Request failed');
    }

    const text = await response.text();
    return text ? JSON.parse(text) : ({} as T);
  }

  async getAllUsers(): Promise<User[]> {
    return this.request<User[]>('/admin/users', {
      method: 'GET',
    });
  }
}

export const userService = new UserService(import.meta.env.VITE_API_URL || 'http://localhost:8080');
