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
describe('authService', () => {
  const fetchMock = vi.fn();
  let authService: AuthService;

  beforeEach(() => {
    authService = new AuthService(API_BASE_URL);
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
        text: async () => JSON.stringify(responseData),
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
      const responseData = { requirePasswordChange: false };
      fetchMock.mockResolvedValueOnce({
        ok: true,
        text: async () => JSON.stringify(responseData),
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
        json: async () => ({}),
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

      await expect(authService.login(loginData)).rejects.toThrow('An error occurred');
    });
  });

  describe('authenticated requests', () => {
    const changePasswordData: ChangePasswordRequest = { oldPassword: 'old', newPassword: 'new' };

    it('includes Authorization header if token exists', async () => {
      localStorage.setItem('authToken', 'stored-token');
      fetchMock.mockResolvedValueOnce({ ok: true, text: async () => '{}' });

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
      fetchMock.mockResolvedValueOnce({ ok: true, text: async () => '{}' });

      await authService.changePassword(changePasswordData);

      const calls = fetchMock.mock.calls[0];
      const options = calls[1];
      expect(options.headers).not.toHaveProperty('Authorization');
    });
  });

  describe('changePassword', () => {
    it('sends change password request', async () => {
      const data: ChangePasswordRequest = { oldPassword: 'old', newPassword: 'new' };
      fetchMock.mockResolvedValueOnce({ ok: true, text: async () => '{}' });

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
      fetchMock.mockResolvedValueOnce({ ok: true, text: async () => '{}' });

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
      fetchMock.mockResolvedValueOnce({ ok: true, text: async () => '{}' });

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
      fetchMock.mockResolvedValueOnce({ ok: true, text: async () => '{}' });

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

  describe('getUserRole', () => {
    it('returns null if no token exists', () => {
      localStorage.removeItem('authToken');
      expect(authService.getUserRole()).toBeNull();
    });

    it('returns role from valid token', () => {
      // Mock a simplified JWT token structures (header.payload.signature)
      const payload = btoa(JSON.stringify({ role: 'admin' }));
      const token = `header.${payload}.signature`;
      localStorage.setItem('authToken', token);

      expect(authService.getUserRole()).toBe('admin');
    });

    it('returns null if token has no role', () => {
      const payload = btoa(JSON.stringify({ some: 'data' }));
      const token = `header.${payload}.signature`;
      localStorage.setItem('authToken', token);

      expect(authService.getUserRole()).toBeNull();
    });

    it('returns null if token is malformed', () => {
      localStorage.setItem('authToken', 'invalid-token');
      expect(authService.getUserRole()).toBeNull();
    });

    it('returns null if payload is not valid json', () => {
      const payload = 'not-json';
      const token = `header.${payload}.signature`;
      localStorage.setItem('authToken', token);

      expect(authService.getUserRole()).toBeNull();
    });

    it('decodes url-safe payloads without padding', () => {
      const encodedPayload = btoa(JSON.stringify({ role: 'editor' }))
        .replace(/\+/g, '-')
        .replace(/\//g, '_')
        .replace(/=+$/g, '');
      const token = `header.${encodedPayload}.signature`;
      localStorage.setItem('authToken', token);

      expect(authService.getUserRole()).toBe('editor');
    });

    it('does not decode malformed token without payload part', () => {
      const atobSpy = vi.spyOn(globalThis, 'atob');
      localStorage.setItem('authToken', 'malformed-token');

      expect(authService.getUserRole()).toBeNull();
      expect(atobSpy).not.toHaveBeenCalled();
    });

    it('decodes token with only two parts', () => {
      const payload = btoa(JSON.stringify({ role: 'admin' }));
      const token = `header.${payload}`;
      localStorage.setItem('authToken', token);

      expect(authService.getUserRole()).toBe('admin');
    });

    it('normalizes base64url payload before decoding', () => {
      const base64Payload = 'eyJyb2xlIjoiYWRtaW4iLCJzdWIiOiJ1IiwieCI6Is+uw5/IlyJ9';
      const base64UrlPayload = base64Payload.replace(/\+/g, '-').replace(/\//g, '_').slice(0, -1);
      const expectedDecodedPayload = `${base64Payload.slice(0, -1)}=`;
      const atobSpy = vi.spyOn(globalThis, 'atob').mockReturnValueOnce(JSON.stringify({ role: 'admin' }));

      localStorage.setItem('authToken', `header.${base64UrlPayload}.signature`);

      expect(authService.getUserRole()).toBe('admin');
      expect(atobSpy).toHaveBeenCalledWith(expectedDecodedPayload);
    });
  });

  describe('getUserId', () => {
    it('returns null when token is missing', () => {
      localStorage.removeItem('authToken');

      expect(authService.getUserId()).toBeNull();
    });

    it('returns sub from valid token', () => {
      const payload = btoa(JSON.stringify({ sub: 'user-123' }));
      const token = `header.${payload}.signature`;
      localStorage.setItem('authToken', token);

      expect(authService.getUserId()).toBe('user-123');
    });

    it('returns null when sub is missing', () => {
      const payload = btoa(JSON.stringify({ role: 'admin' }));
      const token = `header.${payload}.signature`;
      localStorage.setItem('authToken', token);

      expect(authService.getUserId()).toBeNull();
    });

    it('returns null when sub is not a string', () => {
      const payload = btoa(JSON.stringify({ sub: 123 }));
      const token = `header.${payload}.signature`;
      localStorage.setItem('authToken', token);

      expect(authService.getUserId()).toBeNull();
    });
  });
});
