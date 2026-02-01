import React, { useEffect, useState } from 'react';
import { CreateSiteRequest, Site, User } from '@/domain/site/types';
import { createSiteService } from '@/domain/site';
import { createUserService } from '@/domain/user';
import { AssignUserModal } from '@/components/Site/AssignUserModal';
import { SiteForm } from '@/components/Site/SiteForm';
import { Button } from '@/components/Button/Button';

export const SitesPage: React.FC = () => {
  const [sites, setSites] = useState<Site[]>([]);
  const [users, setUsers] = useState<User[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [selectedSite, setSelectedSite] = useState<Site | null>(null);

  // Edit mode state
  const [editingSite, setEditingSite] = useState<Site | null>(null);

  const fetchData = async () => {
    try {
      const [sitesData, usersData] = await Promise.all([
        createSiteService().getSites(),
        createUserService().getAllUsers(),
      ]);
      setSites(sitesData);
      setUsers(usersData);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to fetch data');
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  const handleCreateSite = async (data: CreateSiteRequest) => {
    setIsLoading(true);
    setError(null);
    try {
      if (editingSite) {
        await createSiteService().updateSite(editingSite.id, data);
        setEditingSite(null);
      } else {
        await createSiteService().createSite(data);
      }
      await fetchData(); // Refresh list
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to save site');
    } finally {
      setIsLoading(false);
    }
  };

  const handleDeleteSite = async (site: Site) => {
    if (!window.confirm(`Are you sure you want to delete "${site.name}"?`)) return;

    try {
      await createSiteService().deleteSite(site.id);
      await fetchData();
    } catch (err) {
      alert(err instanceof Error ? err.message : 'Failed to delete site');
    }
  };

  const openAssignModal = (site: Site) => {
    setSelectedSite(site);
    setIsModalOpen(true);
  };

  const handleAssignUser = async (userId: string) => {
    if (!selectedSite) return;
    await createSiteService().assignUser({ userId, siteId: selectedSite.id });
    await fetchData(); // Refresh to update editor count if we display it
  };

  const handleEditClick = (site: Site) => {
    setEditingSite(site);
    setError(null);
  };

  const handleCancelEdit = () => {
    setEditingSite(null);
    setError(null);
  };

  return (
    <div className="container mx-auto px-4 py-8 max-w-7xl">
      <div className="flex items-center justify-between mb-8">
        <div>
          <h1 className="text-3xl font-bold text-gray-900 tracking-tight">Site Management</h1>
          <p className="text-gray-500 mt-1">Manage your sites and assign editors.</p>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div className="lg:col-span-4">
          <div className="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 sticky top-8">
            <h2 className="text-xl font-bold text-gray-900 mb-6">
              {editingSite ? 'Edit Site' : 'Create New Site'}
            </h2>
            <SiteForm
              onSubmit={handleCreateSite}
              isLoading={isLoading}
              initialData={
                editingSite
                  ? { name: editingSite.name, url: editingSite.url, type: editingSite.type }
                  : null
              }
              onCancel={editingSite ? handleCancelEdit : undefined}
              submitLabel={editingSite ? 'Update Site' : 'Create Site'}
            />
            {error && (
              <div className="mt-4 p-3 bg-red-50 text-red-600 text-sm rounded-lg border border-red-100">
                {error}
              </div>
            )}
          </div>
        </div>

        <div className="lg:col-span-8">
          <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div className="p-6 border-b border-gray-100">
              <h2 className="text-xl font-bold text-gray-900">Existing Sites</h2>
            </div>

            {sites.length === 0 ? (
              <div className="p-12 text-center">
                <div className="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 mb-4">
                  <span className="text-2xl">🌐</span>
                </div>
                <h3 className="text-lg font-medium text-gray-900">No sites found</h3>
                <p className="text-gray-500 mt-1">Get started by creating your first site.</p>
              </div>
            ) : (
              <ul className="divide-y divide-gray-100">
                {sites.map((site) => (
                  <li
                    key={site.id}
                    className="p-6 hover:bg-gray-50 transition-colors flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                  >
                    <div>
                      <div className="flex items-center gap-3">
                        <h3 className="text-lg font-semibold text-gray-900">{site.name}</h3>
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
                          <svg
                            className="w-3 h-3"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                          >
                            <path
                              strokeLinecap="round"
                              strokeLinejoin="round"
                              strokeWidth="2"
                              d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                            />
                          </svg>
                        </a>
                        <span className="flex items-center gap-1" title="Assigned Editors">
                          <svg
                            className="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                          >
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
                      <Button size="sm" variant="secondary" onClick={() => openAssignModal(site)}>
                        Assign User
                      </Button>
                      <Button size="sm" variant="secondary" onClick={() => handleEditClick(site)}>
                        Edit
                      </Button>
                      <Button size="sm" variant="danger" onClick={() => handleDeleteSite(site)}>
                        Delete
                      </Button>
                    </div>
                  </li>
                ))}
              </ul>
            )}
          </div>
        </div>
      </div>

      {selectedSite && (
        <AssignUserModal
          isOpen={isModalOpen}
          onClose={() => setIsModalOpen(false)}
          onAssign={handleAssignUser}
          siteName={selectedSite.name}
          users={users}
        />
      )}
    </div>
  );
};
