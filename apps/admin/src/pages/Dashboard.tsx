import React, { useEffect, useState } from 'react';
import { Site } from '@/domain/site/types';
import { siteService } from '@/domain/site';

export const Dashboard: React.FC = () => {
    const [sites, setSites] = useState<Site[]>([]);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        const fetchSites = async () => {
            try {
                const data = await siteService.getAssignedSites();
                setSites(data);
            } catch (error) {
                console.error('Failed to fetch assigned sites', error);
            } finally {
                setIsLoading(false);
            }
        };

        fetchSites();
    }, []);

  return (
    <div className="container mx-auto px-4 py-8">
      <h1 className="text-2xl font-bold mb-6">Dashboard</h1>
      
        {isLoading ? (
            <p>Loading sites...</p>
        ) : (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {sites.map((site) => (
                    <div key={site.id} className="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow duration-300">
                        <div className="px-4 py-5 sm:p-6">
                            <h3 className="text-lg leading-6 font-medium text-gray-900">{site.name}</h3>
                            <div className="mt-2 max-w-xl text-sm text-gray-500">
                                <p>URL: <a href={site.url} target="_blank" rel="noopener noreferrer" className="text-indigo-600 hover:text-indigo-900">{site.url}</a></p>
                                <p className="mt-1">Type: {site.type}</p>
                            </div>
                            <div className="mt-5">
                                <button type="button" className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Manage Site
                                </button>
                            </div>
                        </div>
                    </div>
                ))}
                {sites.length === 0 && (
                    <div className="col-span-full text-center text-gray-500">
                        No sites assigned. Contact an administrator.
                    </div>
                )}
            </div>
        )}
    </div>
  );
};
