import type { Meta, StoryObj } from '@storybook/react';
import { Select } from './Select';

const meta: Meta<typeof Select> = {
  title: 'Components/Select',
  component: Select,
  tags: ['autodocs'],
};

export default meta;
type Story = StoryObj<typeof Select>;

const options = [
  { value: 'admin', label: 'Administrator' },
  { value: 'editor', label: 'Editor' },
  { value: 'viewer', label: 'Viewer' },
];

export const Default: Story = {
  args: {
    label: 'User Role',
    options: options,
  },
};

export const WithPlaceholder: Story = {
  args: {
    label: 'Select Category',
    placeholder: 'Choose a category...',
    options: options,
  },
};

export const WithError: Story = {
  args: {
    label: 'Required Selection',
    options: options,
    error: 'Please select an option to continue',
  },
};

export const Disabled: Story = {
  args: {
    label: 'Disabled Select',
    options: options,
    disabled: true,
  },
};
