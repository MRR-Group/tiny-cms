import type { Meta, StoryObj } from '@storybook/react';
import { Logo } from './Logo';

const meta: Meta<typeof Logo> = {
    title: 'Components/Logo',
    component: Logo,
    tags: ['autodocs'],
};

export default meta;
type Story = StoryObj<typeof Logo>;

export const Branding: Story = {
    args: {
        variant: 'branding',
        subtitle: 'CMS Admin',
    },
};

export const SidebarExpanded: Story = {
    args: {
        variant: 'sidebar-expanded',
        subtitle: 'CMS Admin',
    },
};

export const SidebarCollapsed: Story = {
    args: {
        variant: 'sidebar-collapsed',
    },
};

export const CustomSubtitle: Story = {
    args: {
        variant: 'branding',
        subtitle: 'Management',
    },
};

export const NoSubtitle: Story = {
    args: {
        variant: 'branding',
        subtitle: '',
    },
};
