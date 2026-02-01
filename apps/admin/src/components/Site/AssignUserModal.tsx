import React, { useState } from 'react';
import { User } from '@/domain/site/types';
import { Button } from '../Button/Button';
import { Select } from '../Select/Select';

interface AssignUserModalProps {
  isOpen: boolean;
  onClose: () => void;
  onAssign: (userId: string) => Promise<void>;
  siteName: string;
  users: User[];
}

export const AssignUserModal: React.FC<AssignUserModalProps> = ({
  isOpen,
  onClose,
  onAssign,
  siteName,
  users,
}) => {
  const [userId, setUserId] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);

  if (!isOpen) return null;

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);
    try {
      await onAssign(userId);
      setUserId('');
      onClose();
    } catch (error) {
      console.error(error);
    } finally {
      setIsSubmitting(false);
    }
  };

  const userOptions = users.map((user) => ({
    value: user.id,
    label: `${user.email} (${user.role})`,
  }));

  return (
    <div className="fixed inset-0 bg-gray-600/50 backdrop-blur-sm overflow-y-auto h-full w-full flex items-center justify-center z-50">
      <div className="relative p-8 border w-96 shadow-xl rounded-2xl bg-white">
        <div className="text-center">
          <h3 className="text-xl font-bold text-gray-900 mb-6">Assign User to {siteName}</h3>
          <form onSubmit={handleSubmit} className="text-left space-y-6">
            <Select
              label="Select User"
              value={userId}
              onChange={(e) => setUserId(e.target.value)}
              options={userOptions}
              placeholder="Choose a user..."
              required
            />
            <div className="flex items-center justify-end gap-3 pt-4">
              <Button type="button" variant="ghost" onClick={onClose}>
                Cancel
              </Button>
              <Button type="submit" disabled={isSubmitting || !userId}>
                {isSubmitting ? 'Assigning...' : 'Assign'}
              </Button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
};
