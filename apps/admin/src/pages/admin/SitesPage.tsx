import React, { useEffect, useState } from 'react';
import { CreateSiteRequest, Site } from '@/domain/site/types';
import { createSiteService } from '@/domain/site';
import { SiteForm } from '@/components/Site/SiteForm';
import { SiteList } from '@/components/Site/SiteList';
import { ConfirmActionModal } from '@/components/Modal/ConfirmActionModal';
import { AlertModal } from '@/components/Modal/AlertModal';

export const SitesPage: React.FC = () => {
  const [sites, setSites] = useState<Site[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  // Modals state
  const [editingSite, setEditingSite] = useState<Site | null>(null);
  const [siteToDelete, setSiteToDelete] = useState<Site | null>(null);
  const [alertConfig, setAlertConfig] = useState<{ title: string, message: string, type: 'error' | 'success' } | null>(null);

  const fetchData = async () => {
    try {
      const sitesData = await createSiteService().getSites();
      setSites(sitesData);
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

  const handleConfirmDeleteSite = async () => {
    if (!siteToDelete) return;

    try {
      await createSiteService().deleteSite(siteToDelete.id);
      setSiteToDelete(null);
      await fetchData();
    } catch (err) {
      setAlertConfig({
        title: 'Deletion Failed',
        message: err instanceof Error ? err.message : 'Failed to delete site',
        type: 'error'
      });
    }
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
            <SiteList sites={sites} onEdit={handleEditClick} onDelete={setSiteToDelete} />
          </div>
        </div>
      </div>

      <ConfirmActionModal
        isOpen={!!siteToDelete}
        onClose={() => setSiteToDelete(null)}
        onConfirm={handleConfirmDeleteSite}
        title="Delete Site"
        message={`Are you sure you want to delete "${siteToDelete?.name}"? This action cannot be undone.`}
        confirmLabel="Delete Site"
        variant="danger"
      />

      <AlertModal
        isOpen={!!alertConfig}
        onClose={() => setAlertConfig(null)}
        title={alertConfig?.title || ''}
        message={alertConfig?.message || ''}
        type={alertConfig?.type || 'info'}
      />
    </div>
  );
};
