
import React from 'react';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { SiteForm } from './SiteForm';
import { describe, it, expect, vi } from 'vitest';

describe('SiteForm', () => {
  it('renders correctly', () => {
    render(<SiteForm onSubmit={async () => {}} />);
    expect(screen.getByLabelText(/Name/i)).toBeInTheDocument();
    expect(screen.getByLabelText(/URL/i)).toBeInTheDocument();
    expect(screen.getByText(/Type/i)).toBeInTheDocument();
    expect(screen.getByRole('button', { name: /Create Site/i })).toBeInTheDocument();
  });

  it('submits correctly with user input', async () => {
    const handleSubmit = vi.fn();
    render(<SiteForm onSubmit={handleSubmit} />);

    await userEvent.type(screen.getByLabelText(/Name/i), 'Test Site');
    await userEvent.type(screen.getByLabelText(/URL/i), 'http://example.com');
    await userEvent.selectOptions(screen.getByRole('combobox'), 'dynamic');

    await userEvent.click(screen.getByRole('button', { name: /Create Site/i }));

    expect(handleSubmit).toHaveBeenCalledWith({
      name: 'Test Site',
      url: 'http://example.com',
      type: 'dynamic',
    });
  });

  it('shows loading state', () => {
    render(<SiteForm onSubmit={async () => {}} isLoading={true} />);
    expect(screen.getByRole('button')).toBeDisabled();
    expect(screen.getByRole('button')).toHaveTextContent('Creating...');
  });
});
