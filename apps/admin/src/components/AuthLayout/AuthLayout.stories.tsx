import type { Meta, StoryObj } from '@storybook/react';
import { AuthLayout } from './AuthLayout';

const meta: Meta<typeof AuthLayout> = {
  title: 'Layouts/AuthLayout',
  component: AuthLayout,
  parameters: {
    layout: 'fullscreen',
  },
};

export default meta;
type Story = StoryObj<typeof AuthLayout>;

export const Default: Story = {
  args: {
    subtitle: 'Welcome back! Please login to your account.',
    children: (
      <div className="space-y-4">
        <div className="h-10 bg-slate-100 rounded-lg animate-pulse" />
        <div className="h-10 bg-slate-100 rounded-lg animate-pulse" />
        <div className="h-12 bg-primary/20 rounded-lg animate-pulse" />
      </div>
    ),
  },
};

export const Simple: Story = {
  args: {
    children: (
      <div className="text-center py-4">
        <p className="text-slate-600">Simple content inside AuthLayout card.</p>
      </div>
    ),
  },
};
