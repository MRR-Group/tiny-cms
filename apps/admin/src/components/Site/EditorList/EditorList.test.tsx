import { describe, it, expect, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import { EditorList } from './EditorList';

describe('EditorList', () => {
  const mockEditors = [
    { id: '1', email: 'user1@example.com', role: 'admin' },
    { id: '2', email: 'user2@example.com', role: 'editor' },
  ];

  it('renders a list of editors', () => {
    render(<EditorList editors={mockEditors} onRemove={vi.fn()} />);
    expect(screen.getByText('user1@example.com')).toBeInTheDocument();
    expect(screen.getByText('user2@example.com')).toBeInTheDocument();
  });

  it('renders empty state when no editors are provided', () => {
    render(<EditorList editors={[]} onRemove={vi.fn()} />);
    expect(screen.getByText('No editors assigned.')).toBeInTheDocument();
  });
});
