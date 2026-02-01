import React from 'react';
import { Site } from '@/domain/site/types';
import { SiteItem } from './SiteItem';

interface SiteListProps {
    sites: Site[];
    onEdit: (site: Site) => void;
    onDelete: (site: Site) => void;
}

export const SiteList: React.FC<SiteListProps> = ({ sites, onEdit, onDelete }) => {
    if (sites.length === 0) {
        return (
            <div className="p-12 text-center">
                <div className="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 mb-4">
                    <span className="text-2xl">🌐</span>
                </div>
                <h3 className="text-lg font-medium text-gray-900">No sites found</h3>
                <p className="text-gray-500 mt-1">Get started by creating your first site.</p>
            </div>
        );
    }

    return (
        <ul className="divide-y divide-gray-100">
            {sites.map((site) => (
                <SiteItem key={site.id} site={site} onEdit={onEdit} onDelete={onDelete} />
            ))}
        </ul>
    );
};
