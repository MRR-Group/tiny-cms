import { describe, it, expect, vi } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import { AlertModal } from './AlertModal';

describe('AlertModal', () => {
  const defaultProps = {
    isOpen: true,
    onClose: vi.fn(),
    title: 'Alert Title',
    message: 'Alert Message',
  };

  it('renders correctly when open', () => {
    render(<AlertModal {...defaultProps} />);
    expect(screen.getByText('Alert Title')).toBeInTheDocument();
    expect(screen.getByText('Alert Message')).toBeInTheDocument();
  });

  it('calls onClose when understand button is clicked', () => {
    render(<AlertModal {...defaultProps} />);
    fireEvent.click(screen.getByText('Understand'));
    expect(defaultProps.onClose).toHaveBeenCalled();
  });

  it('renders different types correctly', () => {
    const { rerender } = render(<AlertModal {...defaultProps} type="error" />);
    expect(screen.getByText('❌')).toBeInTheDocument();

    rerender(<AlertModal {...defaultProps} type="success" />);
    expect(screen.getByText('✅')).toBeInTheDocument();

    rerender(<AlertModal {...defaultProps} type="info" />);
    expect(screen.getByText('ℹ️')).toBeInTheDocument();
  });
});
