import React from 'react';
import { User } from '@/domain/site/types';
import { Button } from '@/components/Button/Button';

interface EditorItemProps {
  editor: User;
  onRemove: (userId: string) => void;
}

export const EditorItem: React.FC<EditorItemProps> = ({ editor, onRemove }) => {
  return (
    <div className="py-3 flex justify-between items-center">
      <div>
        <p className="font-medium text-slate-900">{editor.email}</p>
        <p className="text-xs text-slate-500 capitalize">{editor.role}</p>
      </div>
      <Button
        size="sm"
        variant="ghost"
        className="text-red-600 hover:text-red-700 hover:bg-red-50"
        onClick={() => onRemove(editor.id)}
      >
        Remove
      </Button>
    </div>
  );
};
