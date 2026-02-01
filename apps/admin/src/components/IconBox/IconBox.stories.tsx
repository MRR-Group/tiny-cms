import type { Meta, StoryObj } from '@storybook/react';
import { IconBox } from './IconBox';
import HomeIcon from '@/assets/icons/home.svg?react';

const meta: Meta<typeof IconBox> = {
  title: 'Components/IconBox',
  component: IconBox,
  tags: ['autodocs'],
};

export default meta;
type Story = StoryObj<typeof IconBox>;

export const Default: Story = {
  args: {
    children: <HomeIcon className="w-5 h-5" />,
    isActive: false,
  },
};

export const Active: Story = {
  args: {
    children: <HomeIcon className="w-5 h-5" />,
    isActive: true,
  },
};
