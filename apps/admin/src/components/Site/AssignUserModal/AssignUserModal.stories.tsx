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
    onClose: () => { },
    onAssign: async () => new Promise((resolve) => setTimeout(resolve, 1000)),
    users: [
      { id: '1', email: 'alice@example.com', role: 'editor' },
      { id: '2', email: 'bob@example.com', role: 'admin' },
    ],
  },
};

export const Closed: Story = {
  args: {
    isOpen: false,
    siteName: 'My Awesome Site',
    onClose: () => { },
    onAssign: async () => { },
    users: [],
  },
};
