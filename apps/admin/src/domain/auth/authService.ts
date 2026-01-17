export interface LoginRequest {
  email: string;
  password: string;
}

export interface LoginResponse {
  token: string;
  requirePasswordChange?: boolean;
}

export interface ChangePasswordRequest {
  oldPassword: string;
  newPassword: string;
}

export interface CreateUserRequest {
  email: string;
  password: string;
  role: string;
}

export interface PasswordResetRequest {
  email: string;
}

export interface SetNewPasswordRequest {
  token: string;
  password: string;
}

export class AuthService {
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
      throw new Error(error.error?.message || 'Request failed');
    }

    const text = await response.text();
    return text ? JSON.parse(text) : {} as T;
  }

  async login(data: LoginRequest): Promise<LoginResponse> {
    const response = await this.request<LoginResponse>('/auth/login', {
      method: 'POST',
      body: JSON.stringify(data),
    });

    if (response.token) {
      localStorage.setItem('authToken', response.token);
    }

    return response;
  }

  async changePassword(data: ChangePasswordRequest): Promise<void> {
    await this.request('/auth/change-password', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  async createUser(data: CreateUserRequest): Promise<void> {
    await this.request('/admin/users', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  async requestPasswordReset(data: PasswordResetRequest): Promise<void> {
    await this.request('/auth/password-reset/request', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  async setNewPassword(data: SetNewPasswordRequest): Promise<void> {
    await this.request('/auth/password-reset/confirm', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  logout(): void {
    localStorage.removeItem('authToken');
  }

  isAuthenticated(): boolean {
    return !!localStorage.getItem('authToken');
  }

  getToken(): string | null {
    return localStorage.getItem('authToken');
  }
}
