import type { Meta, StoryObj } from '@storybook/react';
import { AlertModal } from './AlertModal';

const meta: Meta<typeof AlertModal> = {
  title: 'Components/Modal/AlertModal',
  component: AlertModal,
  parameters: {
    layout: 'centered',
  },
};

export default meta;
type Story = StoryObj<typeof AlertModal>;

export const Error: Story = {
  args: {
    isOpen: true,
    title: 'Error Occurred',
    message: 'Something went wrong while processing your request.',
    type: 'error',
    onClose: () => console.log('Close'),
  },
};

export const Success: Story = {
  args: {
    isOpen: true,
    title: 'Success!',
    message: 'Your changes have been saved successfully.',
    type: 'success',
    onClose: () => console.log('Close'),
  },
};

export const Info: Story = {
  args: {
    isOpen: true,
    title: 'Information',
    message: 'Please review the new terms and conditions.',
    type: 'info',
    onClose: () => console.log('Close'),
  },
};
