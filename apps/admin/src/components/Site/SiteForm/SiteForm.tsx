import React, { useEffect, useState } from 'react';
import { CreateSiteRequest, SiteType } from '@/domain/site/types';
import { Button } from '@/components/Button/Button';
import { Input } from '@/components/Input/Input';
import { Select } from '@/components/Select/Select';

interface SiteFormProps {
  onSubmit: (data: CreateSiteRequest) => Promise<void>;
  isLoading?: boolean;
  initialData?: CreateSiteRequest | null;
  onCancel?: () => void;
  submitLabel?: string;
}

export const SiteForm: React.FC<SiteFormProps> = ({
  onSubmit,
  isLoading,
  initialData,
  onCancel,
  submitLabel = 'Create Site',
}) => {
  const [name, setName] = useState('');
  const [url, setUrl] = useState('');
  const [type, setType] = useState<SiteType>('static');

  useEffect(() => {
    if (initialData) {
      setName(initialData.name);
      setUrl(initialData.url);
      setType(initialData.type);
    } else {
      setName('');
      setUrl('');
      setType('static');
    }
  }, [initialData]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    let processedUrl = url.trim();

    // Add protocol if missing
    if (!/^https?:\/\//i.test(processedUrl)) {
      processedUrl = `https://${processedUrl}`;
    }

    // Add www. ifhostname only has two parts (e.g., example.com -> www.example.com)
    try {
      const urlObj = new URL(processedUrl);
      const hostname = urlObj.hostname;
      if (!hostname.startsWith('www.') && hostname.split('.').length === 2) {
        urlObj.hostname = `www.${hostname}`;
        processedUrl = urlObj.toString();
      }
    } catch (err) {
      // If URL parsing fails, we still have the protocol-prefixed version
    }

    await onSubmit({ name, url: processedUrl, type });
    if (!initialData) {
      setName('');
      setUrl('');
      setType('static');
    }
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      <Input
        id="name"
        label="Name"
        value={name}
        onChange={(e) => setName(e.target.value)}
        required
        placeholder="My Awesome Site"
      />
      <Input
        id="url"
        label="URL"
        type="text"
        value={url}
        onChange={(e) => setUrl(e.target.value)}
        required
        placeholder="example.com"
      />
      <Select
        id="type"
        label="Type"
        value={type}
        onChange={(e) => setType(e.target.value as SiteType)}
        options={[
          { value: 'static', label: 'Static' },
          { value: 'dynamic', label: 'Dynamic' },
        ]}
      />
      <div className="flex items-center gap-2 pt-2">
        <Button type="submit" disabled={isLoading}>
          {isLoading ? 'Saving...' : submitLabel}
        </Button>
        {onCancel && (
          <Button type="button" variant="ghost" onClick={onCancel}>
            Cancel
          </Button>
        )}
      </div>
    </form>
  );
};
