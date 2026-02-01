import type { Meta, StoryObj } from '@storybook/react';
import { EditorItem } from './EditorItem';

const meta: Meta<typeof EditorItem> = {
  title: 'Components/Site/EditorItem',
  component: EditorItem,
  decorators: [
    (Story) => (
      <div className="max-w-md bg-white p-4 shadow rounded-xl">
        <Story />
      </div>
    ),
  ],
};

export default meta;
type Story = StoryObj<typeof EditorItem>;

export const Admin: Story = {
  args: {
    editor: {
      id: '1',
      email: 'admin@example.com',
      role: 'admin',
    },
    onRemove: (id) => console.log('Remove', id),
  },
};

export const Editor: Story = {
  args: {
    editor: {
      id: '2',
      email: 'editor@example.com',
      role: 'editor',
    },
    onRemove: (id) => console.log('Remove', id),
  },
};
