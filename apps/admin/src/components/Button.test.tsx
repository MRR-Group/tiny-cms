import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it, vi } from 'vitest'
import { Button } from './Button'

describe('Button', () => {
  it('renders label and handles clicks', async () => {
    const onClick = vi.fn()
    render(<Button label="Click me" onClick={onClick} />)

    const button = screen.getByRole('button', { name: 'Click me' })
    await userEvent.click(button)

    expect(button).toBeInTheDocument()
    expect(onClick).toHaveBeenCalledTimes(1)
  })
})
