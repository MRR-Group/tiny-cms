import type { Meta, StoryObj } from '@storybook/react';
import { ConfirmActionModal } from './ConfirmActionModal';

const meta: Meta<typeof ConfirmActionModal> = {
  title: 'Components/Modal/ConfirmActionModal',
  component: ConfirmActionModal,
  parameters: {
    layout: 'centered',
  },
};

export default meta;
type Story = StoryObj<typeof ConfirmActionModal>;

export const Danger: Story = {
  args: {
    isOpen: true,
    title: 'Delete Item',
    message: 'Are you sure you want to delete this item? This action cannot be undone.',
    confirmLabel: 'Delete',
    variant: 'danger',
    onClose: () => console.log('Close'),
    onConfirm: async () => {
      await new Promise((resolve) => setTimeout(resolve, 1000));
      console.log('Confirmed');
    },
  },
};

export const Primary: Story = {
  args: {
    isOpen: true,
    title: 'Publish Changes',
    message: 'Do you want to publish your changes now?',
    confirmLabel: 'Publish',
    variant: 'primary',
    onClose: () => console.log('Close'),
    onConfirm: async () => {
      await new Promise((resolve) => setTimeout(resolve, 1000));
      console.log('Confirmed');
    },
  },
};
