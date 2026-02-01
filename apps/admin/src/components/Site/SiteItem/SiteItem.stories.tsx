import type { Meta, StoryObj } from '@storybook/react';
import { BrowserRouter } from 'react-router-dom';
import { SiteItem } from './SiteItem';

const meta: Meta<typeof SiteItem> = {
  title: 'Components/Site/SiteItem',
  component: SiteItem,
  decorators: [
    (Story) => (
      <BrowserRouter>
        <div className="max-w-2xl bg-white shadow rounded-xl overflow-hidden">
          <Story />
        </div>
      </BrowserRouter>
    ),
  ],
};

export default meta;
type Story = StoryObj<typeof SiteItem>;

export const Static: Story = {
  args: {
    site: {
      id: '1',
      name: 'My Static Site',
      url: 'https://example.com',
      type: 'static',
      createdAt: new Date().toISOString(),
      editorCount: 2,
    },
    onEdit: (site) => console.log('Edit', site),
    onDelete: (site) => console.log('Delete', site),
  },
};

export const Dynamic: Story = {
  args: {
    site: {
      id: '2',
      name: 'My Dynamic App',
      url: 'https://app.example.com',
      type: 'dynamic',
      createdAt: new Date().toISOString(),
      editorCount: 5,
    },
    onEdit: (site) => console.log('Edit', site),
    onDelete: (site) => console.log('Delete', site),
  },
};
