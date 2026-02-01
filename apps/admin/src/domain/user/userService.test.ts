import { describe, it, expect, vi, beforeEach } from 'vitest';
import { UserService, createUserService } from './userService';

const mockFetch = vi.fn();
global.fetch = mockFetch;

describe('UserService', () => {
  let userService: UserService;

  beforeEach(() => {
    userService = new UserService('http://api.com');
    mockFetch.mockClear();
    localStorage.clear();
  });

  it('getAllUsers returns list of users on success', async () => {
    const mockUsers = [{ id: '1', email: 'test@example.com' }];
    const mockResponse = {
      ok: true,
      text: async () => JSON.stringify(mockUsers),
    };
    mockFetch.mockResolvedValue(mockResponse);

    const users = await userService.getAllUsers();

    expect(mockFetch).toHaveBeenCalledWith(
      'http://api.com/admin/users',
      expect.objectContaining({
        method: 'GET',
        headers: expect.objectContaining({ 'Content-Type': 'application/json' }),
      })
    );
    expect(users).toEqual(mockUsers);
  });

  it('getAllUsers sends auth token if present', async () => {
    const mockResponse = {
      ok: true,
      text: async () => '[]',
    };
    mockFetch.mockResolvedValue(mockResponse);
    localStorage.setItem('authToken', 'token123');

    await userService.getAllUsers();

    expect(mockFetch).toHaveBeenCalledWith(
      expect.any(String),
      expect.objectContaining({
        headers: expect.objectContaining({ Authorization: 'Bearer token123' }),
      })
    );
  });

  it('getAllUsers does not send auth token if not present', async () => {
    const mockResponse = {
      ok: true,
      text: async () => '[]',
    };
    mockFetch.mockResolvedValue(mockResponse);

    await userService.getAllUsers();

    const callArgs = mockFetch.mock.calls[0][1];
    expect(callArgs.headers).not.toHaveProperty('Authorization');
  });

  it('getAllUsers throws error on failure', async () => {
    const mockResponse = {
      ok: false,
      json: async () => ({ error: { message: 'Fetch failed' } }),
    };
    mockFetch.mockResolvedValue(mockResponse);

    await expect(userService.getAllUsers()).rejects.toThrow('Fetch failed');
  });

  it('getAllUsers throws error on failure with generic message if no message provided', async () => {
    const mockResponse = {
      ok: false,
      json: async () => ({}),
    };
    mockFetch.mockResolvedValue(mockResponse);

    await expect(userService.getAllUsers()).rejects.toThrow('Request failed');
  });

  it('getAllUsers throws generic error when JSON parsing fails', async () => {
    const mockResponse = {
      ok: false,
      json: async () => {
        throw new Error('Invalid JSON');
      },
    };
    mockFetch.mockResolvedValue(mockResponse);

    await expect(userService.getAllUsers()).rejects.toThrow('An error occurred');
  });

  describe('factory', () => {
    it('createUserService creates instance with correct base URL', () => {
      const instance = createUserService();
      expect(instance).toBeInstanceOf(UserService);
      // Verify base URL is set correctly (default)
      expect((instance as any).baseUrl).toBe('http://localhost:8080');
    });
  });
});
