import type { Meta, StoryObj } from '@storybook/react';
import { BrowserRouter } from 'react-router-dom';
import { SiteList } from './SiteList';

const meta: Meta<typeof SiteList> = {
  title: 'Components/Site/SiteList',
  component: SiteList,
  decorators: [
    (Story) => (
      <BrowserRouter>
        <div className="max-w-4xl bg-white shadow rounded-xl overflow-hidden border border-gray-100">
          <Story />
        </div>
      </BrowserRouter>
    ),
  ],
};

export default meta;
type Story = StoryObj<typeof SiteList>;

export const Default: Story = {
  args: {
    sites: [
      {
        id: '1',
        name: 'Example Site 1',
        url: 'https://example1.com',
        type: 'static',
        createdAt: new Date().toISOString(),
        editorCount: 1,
      },
      {
        id: '2',
        name: 'App Dashboard',
        url: 'https://dashboard.example.com',
        type: 'dynamic',
        createdAt: new Date().toISOString(),
        editorCount: 3,
      },
    ],
    onEdit: (site) => console.log('Edit', site),
    onDelete: (site) => console.log('Delete', site),
  },
};

export const Empty: Story = {
  args: {
    sites: [],
    onEdit: () => {},
    onDelete: () => {},
  },
};
