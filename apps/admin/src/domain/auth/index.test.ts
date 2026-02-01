import { describe, it, expect } from 'vitest';
import { createAuthService } from './index';
import { AuthService } from './authService';

describe('Auth Domain Factory', () => {
  it('createAuthService returns an instance of AuthService', () => {
    const service = createAuthService();
    expect(service).toBeInstanceOf(AuthService);
  });
});
