import type { Meta, StoryObj } from '@storybook/react';
import { PasswordStrengthIndicator } from './PasswordStrengthIndicator';

const meta: Meta<typeof PasswordStrengthIndicator> = {
  title: 'Components/PasswordStrengthIndicator',
  component: PasswordStrengthIndicator,
  tags: ['autodocs'],
};

export default meta;
type Story = StoryObj<typeof PasswordStrengthIndicator>;

export const Weak: Story = {
  args: {
    password: '123',
  },
};

export const Fair: Story = {
  args: {
    password: 'password123',
  },
};

export const Good: Story = {
  args: {
    password: 'StrongPassword123',
  },
};

export const Strong: Story = {
  args: {
    password: 'VeryStrongPassword123!',
  },
};
