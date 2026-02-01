import { AuthService } from './authService';

export * from './authService';

export function createAuthService() {
  return new AuthService(import.meta.env.VITE_API_URL || 'http://localhost:8000');
}
export type {
  LoginRequest,
  LoginResponse,
  ChangePasswordRequest,
  CreateUserRequest,
  PasswordResetRequest,
  SetNewPasswordRequest,
} from './authService';
