import React, { useEffect, useState } from 'react';
import { Site, CreateSiteRequest } from '@/domain/site/types';
import { siteService } from '@/domain/site';
import { AssignUserModal } from '@/components/Site/AssignUserModal';
import { SiteForm } from '@/components/Site/SiteForm';

export const SitesPage: React.FC = () => {
  const [sites, setSites] = useState<Site[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [selectedSite, setSelectedSite] = useState<Site | null>(null);

  const fetchSites = async () => {
    try {
      const data = await siteService.getSites();
      setSites(data);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to fetch sites');
    }
  };

  useEffect(() => {
    fetchSites();
  }, []);

  const handleCreateSite = async (data: CreateSiteRequest) => {
    setIsLoading(true);
    setError(null);
    try {
      await siteService.createSite(data);
      await fetchSites(); // Refresh list
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to create site');
    } finally {
      setIsLoading(false);
    }
  };

  const openAssignModal = (site: Site) => {
      setSelectedSite(site);
      setIsModalOpen(true);
  };

  const handleAssignUser = async (userId: string) => {
      if (!selectedSite) return;
      await siteService.assignUser({ userId, siteId: selectedSite.id });
      // Optionally show success message
  };

  return (
    <div className="container mx-auto px-4 py-8">
      <h1 className="text-2xl font-bold mb-6">Site Management</h1>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div>
          <h2 className="text-xl font-semibold mb-4">Create New Site</h2>
          <div className="bg-white p-6 rounded-lg shadow">
             <SiteForm onSubmit={handleCreateSite} isLoading={isLoading} />
             {error && <p className="text-red-500 mt-2">{error}</p>}
          </div>
        </div>

        <div>
          <h2 className="text-xl font-semibold mb-4">Existing Sites</h2>
          <div className="bg-white rounded-lg shadow overflow-hidden">
            <ul className="divide-y divide-gray-200">
              {sites.map((site) => (
                <li key={site.id} className="p-4 hover:bg-gray-50 flex justify-between items-center">
                  <div>
                    <h3 className="font-medium text-gray-900">{site.name}</h3>
                    <p className="text-sm text-gray-500">{site.url} ({site.type})</p>
                  </div>
                  <button
                    onClick={() => openAssignModal(site)}
                    className="ml-4 bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-sm font-medium hover:bg-indigo-200"
                  >
                    Assign User
                  </button>
                </li>
              ))}
              {sites.length === 0 && (
                <li className="p-4 text-gray-500 text-center">No sites found.</li>
              )}
            </ul>
          </div>
        </div>
      </div>
      
      {selectedSite && (
        <AssignUserModal
            isOpen={isModalOpen}
            onClose={() => setIsModalOpen(false)}
            onAssign={handleAssignUser}
            siteName={selectedSite.name}
        />
      )}
    </div>
  );
};
