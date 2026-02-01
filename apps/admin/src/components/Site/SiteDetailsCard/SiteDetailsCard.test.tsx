import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/react';
import { SiteDetailsCard } from './SiteDetailsCard';

describe('SiteDetailsCard', () => {
  const mockSite = {
    id: '1',
    name: 'Test Site',
    url: 'https://test.com',
    type: 'static' as const,
    createdAt: '2024-01-01T12:00:00Z',
  };

  it('renders site details correctly', () => {
    render(<SiteDetailsCard site={mockSite} />);
    expect(screen.getByText('Site Details')).toBeInTheDocument();
    expect(screen.getByText('static')).toBeInTheDocument();
    // Use regex to match the date as it might depend on the locale
    expect(screen.getByText(/2024|01/)).toBeInTheDocument();
  });
});
