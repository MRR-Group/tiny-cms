import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import {
  AuthService,
  LoginRequest,
  ChangePasswordRequest,
  CreateUserRequest,
  PasswordResetRequest,
  SetNewPasswordRequest,
} from './authService';

const API_BASE_URL = 'http://localhost:8000';
const authService = new AuthService(API_BASE_URL);

describe('authService', () => {
  const fetchMock = vi.fn();

  beforeEach(() => {
    global.fetch = fetchMock;
    const storage = new Map<string, string>();
    vi.stubGlobal('localStorage', {
      getItem: vi.fn((key: string) => storage.get(key) || null),
      setItem: vi.fn((key: string, value: string) => storage.set(key, value)),
      removeItem: vi.fn((key: string) => storage.delete(key)),
    });
  });

  afterEach(() => {
    vi.restoreAllMocks();
  });

  describe('login', () => {
    const loginData: LoginRequest = { email: 'test@example.com', password: 'password' };

    it('performs login request and stores token on success', async () => {
      const responseData = { token: 'jwt-token', requirePasswordChange: false };
      fetchMock.mockResolvedValueOnce({
        ok: true,
        json: async () => responseData,
      });

      const result = await authService.login(loginData);

      expect(fetchMock).toHaveBeenCalledWith(
        `${API_BASE_URL}/auth/login`,
        expect.objectContaining({
          method: 'POST',
          headers: expect.objectContaining({ 'Content-Type': 'application/json' }),
          body: JSON.stringify(loginData),
        })
      );
      expect(localStorage.setItem).toHaveBeenCalledWith('authToken', 'jwt-token');
      expect(result).toEqual(responseData);
    });

    it('does not store token if missing in response', async () => {
      const responseData = { requirePasswordChange: false }; // no token
      fetchMock.mockResolvedValueOnce({
        ok: true,
        json: async () => responseData,
      });

      await authService.login(loginData);

      expect(localStorage.setItem).not.toHaveBeenCalled();
    });

    it('throws error on failure', async () => {
      fetchMock.mockResolvedValueOnce({
        ok: false,
        json: async () => ({ error: { message: 'Invalid credentials' } }),
      });

      await expect(authService.login(loginData)).rejects.toThrow('Invalid credentials');
    });

    it('throws generic error if message is missing', async () => {
      fetchMock.mockResolvedValueOnce({
        ok: false,
        json: async () => ({}), // No error message
      });

      await expect(authService.login(loginData)).rejects.toThrow('Request failed');
    });

    it('handles json parsing error in error response', async () => {
      fetchMock.mockResolvedValueOnce({
        ok: false,
        json: async () => {
          throw new Error('parse error');
        },
      });

      // The catch block in request method returns { error: { message: 'An error occurred' } }
      await expect(authService.login(loginData)).rejects.toThrow('An error occurred');
    });
  });

  describe('authenticated requests', () => {
    // Helper to test private request method logic via a public method like changePassword
    const changePasswordData: ChangePasswordRequest = { oldPassword: 'old', newPassword: 'new' };

    it('includes Authorization header if token exists', async () => {
      localStorage.setItem('authToken', 'stored-token');
      fetchMock.mockResolvedValueOnce({ ok: true, json: async () => ({}) });

      await authService.changePassword(changePasswordData);

      expect(fetchMock).toHaveBeenCalledWith(
        expect.any(String),
        expect.objectContaining({
          headers: expect.objectContaining({
            Authorization: 'Bearer stored-token',
          }),
        })
      );
    });

    it('does not include Authorization header if token does not exist', async () => {
      // storage is empty by default
      fetchMock.mockResolvedValueOnce({ ok: true, json: async () => ({}) });

      await authService.changePassword(changePasswordData);

      const calls = fetchMock.mock.calls[0];
      const options = calls[1];
      expect(options.headers).not.toHaveProperty('Authorization');
    });
  });

  describe('changePassword', () => {
    it('sends change password request', async () => {
      const data: ChangePasswordRequest = { oldPassword: 'old', newPassword: 'new' };
      fetchMock.mockResolvedValueOnce({ ok: true, json: async () => ({}) });

      await authService.changePassword(data);

      expect(fetchMock).toHaveBeenCalledWith(
        `${API_BASE_URL}/auth/change-password`,
        expect.objectContaining({
          method: 'POST',
          body: JSON.stringify(data),
        })
      );
    });
  });

  describe('createUser', () => {
    it('sends create user request', async () => {
      const data: CreateUserRequest = { email: 'new@example.com', password: 'pass', role: 'admin' };
      fetchMock.mockResolvedValueOnce({ ok: true, json: async () => ({}) });

      await authService.createUser(data);

      expect(fetchMock).toHaveBeenCalledWith(
        `${API_BASE_URL}/admin/users`,
        expect.objectContaining({
          method: 'POST',
          body: JSON.stringify(data),
        })
      );
    });
  });

  describe('requestPasswordReset', () => {
    it('sends password reset request', async () => {
      const data: PasswordResetRequest = { email: 'reset@example.com' };
      fetchMock.mockResolvedValueOnce({ ok: true, json: async () => ({}) });

      await authService.requestPasswordReset(data);

      expect(fetchMock).toHaveBeenCalledWith(
        `${API_BASE_URL}/auth/password-reset/request`,
        expect.objectContaining({
          method: 'POST',
          body: JSON.stringify(data),
        })
      );
    });
  });

  describe('setNewPassword', () => {
    it('sends set new password request', async () => {
      const data: SetNewPasswordRequest = { token: 'reset-token', password: 'new-pass' };
      fetchMock.mockResolvedValueOnce({ ok: true, json: async () => ({}) });

      await authService.setNewPassword(data);

      expect(fetchMock).toHaveBeenCalledWith(
        `${API_BASE_URL}/auth/password-reset/confirm`,
        expect.objectContaining({
          method: 'POST',
          body: JSON.stringify(data),
        })
      );
    });
  });

  describe('token management', () => {
    it('logout removes token', () => {
      localStorage.setItem('authToken', 'token');
      authService.logout();
      expect(localStorage.removeItem).toHaveBeenCalledWith('authToken');
      expect(localStorage.getItem('authToken')).toBeNull();
    });

    it('isAuthenticated returns true if token exists', () => {
      localStorage.setItem('authToken', 'token');
      expect(authService.isAuthenticated()).toBe(true);
    });

    it('isAuthenticated returns false if token missing', () => {
      expect(authService.isAuthenticated()).toBe(false);
    });

    it('getToken returns token from local storage', () => {
      localStorage.setItem('authToken', 'token');
      expect(authService.getToken()).toBe('token');
    });

    it('getToken returns null if missing', () => {
      expect(authService.getToken()).toBeNull();
    });
  });
});
