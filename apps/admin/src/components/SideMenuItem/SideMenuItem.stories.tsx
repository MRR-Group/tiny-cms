import type { Meta, StoryObj } from '@storybook/react';
import { SideMenuItem } from './SideMenuItem';
import HomeIcon from '@/assets/icons/home.svg?react';

const meta: Meta<typeof SideMenuItem> = {
    title: 'Components/SideMenuItem',
    component: SideMenuItem,
    tags: ['autodocs'],
    decorators: [
        (Story) => (
            <div className="w-64 bg-white p-4">
                <Story />
            </div>
        ),
    ],
};

export default meta;
type Story = StoryObj<typeof SideMenuItem>;

export const Default: Story = {
    args: {
        icon: HomeIcon,
        label: 'Dashboard',
        isActive: false,
        isCollapsed: false,
    },
};

export const Active: Story = {
    args: {
        icon: HomeIcon,
        label: 'Dashboard',
        isActive: true,
        isCollapsed: false,
    },
};

export const Collapsed: Story = {
    decorators: [
        (Story) => (
            <div className="w-20 bg-white p-4">
                <div className="flex justify-center">
                    <Story />
                </div>
            </div>
        ),
    ],
    args: {
        icon: HomeIcon,
        label: 'Dashboard',
        isCollapsed: true,
        isActive: false,
    },
};
