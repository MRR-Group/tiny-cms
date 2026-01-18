import type { Meta, StoryObj } from '@storybook/react';
import { AssignUserModal } from './AssignUserModal';

const meta: Meta<typeof AssignUserModal> = {
  title: 'Site/AssignUserModal',
  component: AssignUserModal,
  tags: ['autodocs'],
};

export default meta;
type Story = StoryObj<typeof AssignUserModal>;

export const Default: Story = {
  args: {
    isOpen: true,
    siteName: 'My Awesome Site',
    onClose: () => {},
    onAssign: async () => new Promise((resolve) => setTimeout(resolve, 1000)),
  },
};

export const Closed: Story = {
  args: {
    isOpen: false,
    siteName: 'My Awesome Site',
    onClose: () => {},
    onAssign: async () => {},
  },
};
