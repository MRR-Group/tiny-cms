import { describe, it, expect, vi } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import { BrowserRouter } from 'react-router-dom';
import { SiteItem } from './SiteItem';

describe('SiteItem', () => {
  const mockSite = {
    id: '1',
    name: 'Test Site',
    url: 'https://test.com',
    type: 'static' as const,
    createdAt: new Date().toISOString(),
    editorCount: 5,
  };

  const defaultProps = {
    site: mockSite,
    onEdit: vi.fn(),
    onDelete: vi.fn(),
  };

  const renderWithRouter = (ui: React.ReactElement) => {
    return render(ui, { wrapper: BrowserRouter });
  };

  it('renders site information correctly', () => {
    renderWithRouter(<SiteItem {...defaultProps} />);
    expect(screen.getByText('Test Site')).toBeInTheDocument();
    expect(screen.getByText('https://test.com')).toBeInTheDocument();
    expect(screen.getByText('static')).toBeInTheDocument();
    expect(screen.getByText('5 Editors')).toBeInTheDocument();
  });

  it('calls onEdit when edit button is clicked', () => {
    renderWithRouter(<SiteItem {...defaultProps} />);
    fireEvent.click(screen.getByText('Edit'));
    expect(defaultProps.onEdit).toHaveBeenCalledWith(mockSite);
  });

  it('calls onDelete when delete button is clicked', () => {
    renderWithRouter(<SiteItem {...defaultProps} />);
    fireEvent.click(screen.getByText('Delete'));
    expect(defaultProps.onDelete).toHaveBeenCalledWith(mockSite);
  });

  it('links to the correct details page', () => {
    renderWithRouter(<SiteItem {...defaultProps} />);
    expect(screen.getByRole('link', { name: 'Test Site' })).toHaveAttribute(
      'href',
      '/admin/sites/1'
    );
  });
});
