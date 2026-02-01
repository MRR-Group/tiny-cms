import '@testing-library/jest-dom';
import { vi } from 'vitest';

// Mock SVG components
vi.mock('@/assets/icons/users.svg?react', () => ({
  default: () => 'svg-mock',
}));
