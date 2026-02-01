import type { Meta, StoryObj } from '@storybook/react';
import { SiteForm } from './SiteForm';

const meta: Meta<typeof SiteForm> = {
  title: 'Components/Site/SiteForm',
  component: SiteForm,
  decorators: [
    (Story) => (
      <div className="max-w-md bg-white p-6 shadow rounded-xl">
        <Story />
      </div>
    ),
  ],
};

export default meta;
type Story = StoryObj<typeof SiteForm>;

export const Create: Story = {
  args: {
    onSubmit: async (data) => {
      await new Promise((r) => setTimeout(r, 1000));
      console.log('Submit', data);
    },
    submitLabel: 'Create Site',
  },
};

export const Edit: Story = {
  args: {
    initialData: {
      name: 'Existing Site',
      url: 'https://www.existing.com/',
      type: 'static',
    },
    onSubmit: async (data) => {
      await new Promise((r) => setTimeout(r, 1000));
      console.log('Update', data);
    },
    onCancel: () => console.log('Cancel'),
    submitLabel: 'Save Changes',
  },
};
