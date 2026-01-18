import type { Meta, StoryObj } from '@storybook/react';
import { SiteForm } from './SiteForm';

const meta: Meta<typeof SiteForm> = {
  title: 'Site/SiteForm',
  component: SiteForm,
  tags: ['autodocs'],
};

export default meta;
type Story = StoryObj<typeof SiteForm>;

export const Default: Story = {
  args: {
    onSubmit: async () => new Promise((resolve) => setTimeout(resolve, 1000)),
    isLoading: false,
  },
};

export const Loading: Story = {
  args: {
    onSubmit: async () => {},
    isLoading: true,
  },
};
