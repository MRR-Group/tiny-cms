import React from 'react';
import { User } from '@/domain/site/types';
import { EditorItem } from '../EditorItem';
import UsersIcon from '@/assets/icons/users.svg?react';

interface EditorListProps {
  editors?: User[];
  onRemove: (userId: string) => void;
}

export const EditorList: React.FC<EditorListProps> = ({ editors, onRemove }) => {
  return (
    <div className="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm">
      <h2 className="text-xl font-bold mb-4 flex items-center gap-2">
        <UsersIcon className="w-5 h-5 text-slate-400" />
        Editors
      </h2>

      {editors && editors.length > 0 ? (
        <div className="divide-y divide-slate-100">
          {editors.map((editor) => (
            <EditorItem key={editor.id} editor={editor} onRemove={onRemove} />
          ))}
        </div>
      ) : (
        <p className="text-slate-500 italic">No editors assigned.</p>
      )}
    </div>
  );
};
