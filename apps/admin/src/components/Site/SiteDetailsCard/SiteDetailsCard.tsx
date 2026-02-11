import React from 'react';
import { Site } from '@/domain/site/types';

interface SiteDetailsCardProps {
  site: Site;
}

export const SiteDetailsCard: React.FC<SiteDetailsCardProps> = ({ site }) => {
  return (
    <div className="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm">
      <h3 className="font-bold text-slate-700 mb-2">Site Details</h3>
      <dl className="space-y-2 text-sm">
        <div>
          <dt className="text-slate-500">Type</dt>
          <dd className="font-medium capitalize">{site.type}</dd>
        </div>
        <div>
          <dt className="text-slate-500">Created At</dt>
          <dd className="font-medium">{new Date(site.createdAt).toLocaleDateString()}</dd>
        </div>
      </dl>
    </div>
  );
};
