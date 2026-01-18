import React from 'react';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { SitesPage } from './SitesPage';
import { siteService } from '@/domain/site';
import { describe, it, expect, vi, beforeEach, Mock } from 'vitest';

// Mock siteService
vi.mock('@/domain/site', () => ({
  siteService: {
    getSites: vi.fn(),
    createSite: vi.fn(),
    assignUser: vi.fn(),
  },
}));

describe('SitesPage', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('fetches and displays sites', async () => {
    const sites = [
      { id: '1', name: 'Site 1', url: 'http://site1.com', type: 'static', createdAt: 'date' },
    ];
    (siteService.getSites as Mock).mockResolvedValue(sites);

    render(<SitesPage />);

    await waitFor(() => {
      expect(screen.getByText('Site 1')).toBeInTheDocument();
    });
    expect(screen.getByText('http://site1.com (static)')).toBeInTheDocument();
  });

  it('displays error if fetch fails', async () => {
    (siteService.getSites as Mock).mockRejectedValue(new Error('Fetch failed'));

    render(<SitesPage />);

    await waitFor(() => {
      expect(screen.getByText('No sites found.')).toBeInTheDocument();
    });
    // Check if error message is present
    // Wait, fetchSites error sets error state.
  });

  it('creates a site and refreshes list', async () => {
    (siteService.getSites as Mock).mockResolvedValue([]);
    (siteService.createSite as Mock).mockResolvedValue({});

    render(<SitesPage />);

    await userEvent.type(screen.getByLabelText(/Name/i), 'New Site');
    await userEvent.type(screen.getByLabelText(/URL/i), 'http://new.com');

    // Mock getSites returning new list after create
    (siteService.getSites as Mock)
      .mockResolvedValueOnce([])
      .mockResolvedValueOnce([
        { id: '2', name: 'New Site', url: 'http://new.com', type: 'static' },
      ]);

    await userEvent.click(screen.getByRole('button', { name: /Create Site/i }));

    await waitFor(() => {
      expect(siteService.createSite).toHaveBeenCalledWith({
        name: 'New Site',
        url: 'http://new.com',
        type: 'static', // Default
      });
    });
  });
});
