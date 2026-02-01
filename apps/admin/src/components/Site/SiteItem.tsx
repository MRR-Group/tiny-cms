import React from 'react';
import { Link } from 'react-router-dom';
import { Site } from '@/domain/site/types';
import { Button } from '@/components/Button/Button';

interface SiteItemProps {
    site: Site;
    onEdit: (site: Site) => void;
    onDelete: (site: Site) => void;
}

export const SiteItem: React.FC<SiteItemProps> = ({ site, onEdit, onDelete }) => {
    return (
        <li className="p-6 hover:bg-gray-50 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div className="flex items-center gap-3">
                    <Link
                        to={`/admin/sites/${site.id}`}
                        className="text-lg font-semibold text-gray-900 hover:text-indigo-600 hover:underline"
                    >
                        {site.name}
                    </Link>
                    <span
                        className={`px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wide
            ${site.type === 'static' ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700'}`}
                    >
                        {site.type}
                    </span>
                </div>
                <div className="text-sm text-gray-500 mt-1 flex items-center gap-4">
                    <a
                        href={site.url}
                        target="_blank"
                        rel="noreferrer"
                        className="hover:text-indigo-600 transition-colors flex items-center gap-1"
                    >
                        {site.url}
                        <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth="2"
                                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                            />
                        </svg>
                    </a>
                    <span className="flex items-center gap-1" title="Assigned Editors">
                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                            />
                        </svg>
                        {site.editorCount} Editors
                    </span>
                </div>
            </div>

            <div className="flex items-center gap-2">
                <Button size="sm" variant="secondary" onClick={() => onEdit(site)}>
                    Edit
                </Button>
                <Button size="sm" variant="danger" onClick={() => onDelete(site)}>
                    Delete
                </Button>
            </div>
        </li>
    );
};
