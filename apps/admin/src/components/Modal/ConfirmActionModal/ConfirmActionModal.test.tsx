import { describe, it, expect, vi } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { ConfirmActionModal } from './ConfirmActionModal';

describe('ConfirmActionModal', () => {
  const defaultProps = {
    isOpen: true,
    onClose: vi.fn(),
    onConfirm: vi.fn().mockResolvedValue(undefined),
    title: 'Confirm Title',
    message: 'Confirm Message',
  };

  it('renders correctly when open', () => {
    render(<ConfirmActionModal {...defaultProps} />);
    expect(screen.getByText('Confirm Title')).toBeInTheDocument();
    expect(screen.getByText('Confirm Message')).toBeInTheDocument();
  });

  it('does not render when closed', () => {
    render(<ConfirmActionModal {...defaultProps} isOpen={false} />);
    expect(screen.queryByText('Confirm Title')).not.toBeInTheDocument();
  });

  it('calls onClose when cancel button is clicked', () => {
    render(<ConfirmActionModal {...defaultProps} />);
    fireEvent.click(screen.getByText('Cancel'));
    expect(defaultProps.onClose).toHaveBeenCalled();
  });

  it('calls onConfirm and then onClose when confirm button is clicked', async () => {
    render(<ConfirmActionModal {...defaultProps} />);
    fireEvent.click(screen.getByText('Confirm'));

    expect(defaultProps.onConfirm).toHaveBeenCalled();
    await waitFor(() => {
      expect(defaultProps.onClose).toHaveBeenCalled();
    });
  });

  it('shows processing state while submitting', async () => {
    let resolveConfirm: (value: void | PromiseLike<void>) => void;
    const slowConfirm = new Promise<void>((resolve) => {
      resolveConfirm = resolve;
    });

    render(<ConfirmActionModal {...defaultProps} onConfirm={() => slowConfirm} />);

    fireEvent.click(screen.getByText('Confirm'));

    expect(screen.getByText('Processing...')).toBeInTheDocument();
    expect(screen.getByText('Processing...')).toBeDisabled();

    resolveConfirm!();

    await waitFor(() => {
      expect(screen.queryByText('Processing...')).not.toBeInTheDocument();
    });
  });
});
