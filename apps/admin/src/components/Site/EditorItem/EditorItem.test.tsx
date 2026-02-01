import { describe, it, expect, vi } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import { EditorItem } from './EditorItem';

describe('EditorItem', () => {
  const mockEditor = {
    id: '1',
    email: 'test@example.com',
    role: 'editor',
  };

  const defaultProps = {
    editor: mockEditor,
    onRemove: vi.fn(),
  };

  it('renders editor information correctly', () => {
    render(<EditorItem {...defaultProps} />);
    expect(screen.getByText('test@example.com')).toBeInTheDocument();
    expect(screen.getByText('editor')).toBeInTheDocument();
  });

  it('calls onRemove when remove button is clicked', () => {
    render(<EditorItem {...defaultProps} />);
    fireEvent.click(screen.getByText('Remove'));
    expect(defaultProps.onRemove).toHaveBeenCalledWith('1');
  });
});
