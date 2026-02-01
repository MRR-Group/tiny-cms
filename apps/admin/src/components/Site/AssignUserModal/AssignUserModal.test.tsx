import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { AssignUserModal } from './AssignUserModal';

describe('AssignUserModal', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });
  const mockUsers = [
    { id: '1', email: 'user1@example.com', role: 'editor' },
    { id: '2', email: 'user2@example.com', role: 'editor' },
  ];

  const defaultProps = {
    isOpen: true,
    onClose: vi.fn(),
    onAssign: vi.fn().mockResolvedValue(undefined),
    siteName: 'Test Site',
    users: mockUsers,
  };

  it('renders correctly', () => {
    render(<AssignUserModal {...defaultProps} />);
    expect(screen.getByText('Assign User to Test Site')).toBeInTheDocument();
    expect(screen.getByText(/user1@example.com/)).toBeInTheDocument();
    expect(screen.getByText(/user2@example.com/)).toBeInTheDocument();
  });

  it('calls onAssign with selected user id', async () => {
    render(<AssignUserModal {...defaultProps} />);

    await userEvent.selectOptions(screen.getByRole('combobox'), '2');
    await userEvent.click(screen.getByText('Assign'));

    expect(defaultProps.onAssign).toHaveBeenCalledWith('2');
    await waitFor(() => {
      expect(defaultProps.onClose).toHaveBeenCalled();
    });
  });

  it('shows error if no user is selected', async () => {
    render(<AssignUserModal {...defaultProps} />);

    await userEvent.click(screen.getByText('Assign'));

    expect(screen.getByText('Please select a user')).toBeInTheDocument();
    expect(defaultProps.onAssign).not.toHaveBeenCalled();
  });
});
