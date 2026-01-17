import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { authService, LoginRequest, ChangePasswordRequest, CreateUserRequest, PasswordResetRequest, SetNewPasswordRequest } from './authService';

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000';

describe('authService', () => {
    const fetchMock = vi.fn();

    beforeEach(() => {
        global.fetch = fetchMock;
        vi.stubGlobal('localStorage', {
            getItem: vi.fn(),
            setItem: vi.fn(),
            removeItem: vi.fn(),
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

            expect(fetchMock).toHaveBeenCalledWith(`${API_BASE_URL}/auth/login`, expect.objectContaining({
                method: 'POST',
                headers: expect.objectContaining({ 'Content-Type': 'application/json' }),
                body: JSON.stringify(loginData),
            }));
            expect(localStorage.setItem).toHaveBeenCalledWith('authToken', 'jwt-token');
            expect(result).toEqual(responseData);
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
                json: async () => { throw new Error('parse error') },
            });

            // The catch block in request method returns { error: { message: 'An error occurred' } }
            await expect(authService.login(loginData)).rejects.toThrow('An error occurred');
        });
    });

    describe('authenticated requests', () => {
        // Helper to test private request method logic via a public method like changePassword
        const changePasswordData: ChangePasswordRequest = { oldPassword: 'old', newPassword: 'new' };

        it('includes Authorization header if token exists', async () => {
            vi.mocked(localStorage.getItem).mockReturnValue('stored-token');
            fetchMock.mockResolvedValueOnce({ ok: true, json: async () => ({}) });

            await authService.changePassword(changePasswordData);

            expect(fetchMock).toHaveBeenCalledWith(expect.any(String), expect.objectContaining({
                headers: expect.objectContaining({
                    'Authorization': 'Bearer stored-token',
                }),
            }));
        });

        it('does not include Authorization header if token does not exist', async () => {
            vi.mocked(localStorage.getItem).mockReturnValue(null);
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

            expect(fetchMock).toHaveBeenCalledWith(`${API_BASE_URL}/auth/change-password`, expect.objectContaining({
                method: 'POST',
                body: JSON.stringify(data),
            }));
        });
    });

    describe('createUser', () => {
        it('sends create user request', async () => {
            const data: CreateUserRequest = { email: 'new@example.com', password: 'pass', role: 'admin' };
            fetchMock.mockResolvedValueOnce({ ok: true, json: async () => ({}) });

            await authService.createUser(data);

            expect(fetchMock).toHaveBeenCalledWith(`${API_BASE_URL}/admin/users`, expect.objectContaining({
                method: 'POST',
                body: JSON.stringify(data),
            }));
        });
    });

    describe('requestPasswordReset', () => {
        it('sends password reset request', async () => {
            const data: PasswordResetRequest = { email: 'reset@example.com' };
            fetchMock.mockResolvedValueOnce({ ok: true, json: async () => ({}) });

            await authService.requestPasswordReset(data);

            expect(fetchMock).toHaveBeenCalledWith(`${API_BASE_URL}/auth/password-reset/request`, expect.objectContaining({
                method: 'POST',
                body: JSON.stringify(data),
            }));
        });
    });

    describe('setNewPassword', () => {
        it('sends set new password request', async () => {
            const data: SetNewPasswordRequest = { token: 'reset-token', password: 'new-pass' };
            fetchMock.mockResolvedValueOnce({ ok: true, json: async () => ({}) });

            await authService.setNewPassword(data);

            expect(fetchMock).toHaveBeenCalledWith(`${API_BASE_URL}/auth/password-reset/confirm`, expect.objectContaining({
                method: 'POST',
                body: JSON.stringify(data),
            }));
        });
    });

    describe('token management', () => {
        it('logout removes token', () => {
            authService.logout();
            expect(localStorage.removeItem).toHaveBeenCalledWith('authToken');
        });

        it('isAuthenticated returns true if token exists', () => {
            vi.mocked(localStorage.getItem).mockReturnValue('token');
            expect(authService.isAuthenticated()).toBe(true);
        });

        it('isAuthenticated returns false if token missing', () => {
            vi.mocked(localStorage.getItem).mockReturnValue(null);
            expect(authService.isAuthenticated()).toBe(false);
        });

        it('getToken returns token from local storage', () => {
            vi.mocked(localStorage.getItem).mockReturnValue('token');
            expect(authService.getToken()).toBe('token');
        });

        it('getToken returns null if missing', () => {
            vi.mocked(localStorage.getItem).mockReturnValue(null);
            expect(authService.getToken()).toBeNull();
        });
    });
});
