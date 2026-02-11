import type { Meta, StoryObj } from '@storybook/react';
import { SiteDetailsCard } from './SiteDetailsCard';

const meta: Meta<typeof SiteDetailsCard> = {
  title: 'Components/Site/SiteDetailsCard',
  component: SiteDetailsCard,
  decorators: [
    (Story) => (
      <div className="max-w-sm p-4">
        <Story />
      </div>
    ),
  ],
};

export default meta;
type Story = StoryObj<typeof SiteDetailsCard>;

export const Default: Story = {
  args: {
    site: {
      id: '1',
      name: 'Example Site',
      url: 'https://example.com',
      type: 'static',
      editorCount: 0,
      createdAt: '2024-01-01T12:00:00Z',
    },
  },
};
