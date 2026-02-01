import { describe, it, expect, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import { BrowserRouter } from 'react-router-dom';
import { SiteList } from './SiteList';

describe('SiteList', () => {
  const mockSites = [
    {
      id: '1',
      name: 'Site 1',
      url: 'https://site1.com',
      type: 'static' as const,
      createdAt: new Date().toISOString(),
      editorCount: 1,
    },
    {
      id: '2',
      name: 'Site 2',
      url: 'https://site2.com',
      type: 'dynamic' as const,
      createdAt: new Date().toISOString(),
      editorCount: 2,
    },
  ];

  const defaultProps = {
    sites: mockSites,
    onEdit: vi.fn(),
    onDelete: vi.fn(),
  };

  const renderWithRouter = (ui: React.ReactElement) => {
    return render(ui, { wrapper: BrowserRouter });
  };

  it('renders a list of sites', () => {
    renderWithRouter(<SiteList {...defaultProps} />);
    expect(screen.getByText('Site 1')).toBeInTheDocument();
    expect(screen.getByText('Site 2')).toBeInTheDocument();
  });

  it('renders empty state when no sites are provided', () => {
    renderWithRouter(<SiteList {...defaultProps} sites={[]} />);
    expect(screen.getByText('No sites found')).toBeInTheDocument();
  });
});
