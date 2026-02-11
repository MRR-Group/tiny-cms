import React from 'react';
import { createPortal } from 'react-dom';
import { Button } from '@/components/Button/Button';

interface AlertModalProps {
  isOpen: boolean;
  onClose: () => void;
  title: string;
  message: string;
  type?: 'error' | 'success' | 'info';
}

export const AlertModal: React.FC<AlertModalProps> = ({
  isOpen,
  onClose,
  title,
  message,
  type = 'info',
}) => {
  if (!isOpen) return null;

  const typeConfig = {
    error: {
      icon: '❌',
      bg: 'bg-red-50',
      text: 'text-red-800',
      titleColor: 'text-red-900',
    },
    success: {
      icon: '✅',
      bg: 'bg-green-50',
      text: 'text-green-800',
      titleColor: 'text-green-900',
    },
    info: {
      icon: 'ℹ️',
      bg: 'bg-blue-50',
      text: 'text-blue-800',
      titleColor: 'text-blue-900',
    },
  };

  const config = typeConfig[type];

  return createPortal(
    <div className="fixed inset-0 bg-gray-600/50 backdrop-blur-sm overflow-y-auto h-full w-full flex items-center justify-center z-[60]">
      <div className="relative p-8 border w-96 shadow-xl rounded-2xl bg-white animate-in fade-in zoom-in-95 duration-200">
        <div className="text-center">
          <div
            className={`mx-auto flex items-center justify-center h-12 w-12 rounded-full ${config.bg} mb-4 text-2xl`}
          >
            {config.icon}
          </div>
          <h3 className={`text-xl font-bold ${config.titleColor} mb-2`}>{title}</h3>
          <p className={`text-sm ${config.text} mb-8`}>{message}</p>
          <div className="flex items-center justify-center">
            <Button type="button" variant="primary" onClick={onClose} className="w-full">
              Understand
            </Button>
          </div>
        </div>
      </div>
    </div>,
    document.body
  );
};
