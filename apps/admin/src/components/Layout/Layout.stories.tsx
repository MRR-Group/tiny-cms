import type { Meta, StoryObj } from '@storybook/react';
import { Layout } from './Layout';
import { BrowserRouter } from 'react-router-dom';

const meta: Meta<typeof Layout> = {
  title: 'Layouts/MainLayout',
  component: Layout,
  parameters: {
    layout: 'fullscreen',
  },
  decorators: [
    (Story) => (
      <BrowserRouter>
        <Story />
      </BrowserRouter>
    ),
  ],
};

export default meta;
type Story = StoryObj<typeof Layout>;

export const Default: Story = {
  render: () => <Layout />,
};
