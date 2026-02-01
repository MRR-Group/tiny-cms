import type { Meta, StoryObj } from '@storybook/react';
import { EditorList } from './EditorList';

const meta: Meta<typeof EditorList> = {
  title: 'Components/Site/EditorList',
  component: EditorList,
  decorators: [
    (Story) => (
      <div className="max-w-2xl bg-gray-50 p-8 min-h-[300px]">
        <Story />
      </div>
    ),
  ],
};

export default meta;
type Story = StoryObj<typeof EditorList>;

export const Default: Story = {
  args: {
    editors: [
      { id: '1', email: 'john@example.com', role: 'admin' },
      { id: '2', email: 'jane@example.com', role: 'editor' },
      { id: '3', email: 'bob@example.com', role: 'editor' },
    ],
    onRemove: (id) => console.log('Remove', id),
  },
};

export const Empty: Story = {
  args: {
    editors: [],
    onRemove: () => {},
  },
};
