import React, { useEffect, useState, useCallback } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { CreateSiteRequest, Site, User } from '@/domain/site/types';
import { createSiteService } from '@/domain/site';
import { createUserService } from '@/domain/user/userService';
import { Button } from '@/components/Button/Button';
import { AssignUserModal } from '@/components/Site/AssignUserModal';
import { SiteForm } from '@/components/Site/SiteForm';
import { EditorList } from '@/components/Site/EditorList';
import { SiteDetailsCard } from '@/components/Site/SiteDetailsCard';
import { ConfirmActionModal } from '@/components/Modal/ConfirmActionModal';
import { AlertModal } from '@/components/Modal/AlertModal';

export const SiteDetailsPage: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const [site, setSite] = useState<Site | null>(null);
  const [users, setUsers] = useState<User[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  // Modals state
  const [isAssignModalOpen, setIsAssignModalOpen] = useState(false);
  const [isEditing, setIsEditing] = useState(false);
  const [userToRemove, setUserToRemove] = useState<string | null>(null);
  const [isDeleteModalOpen, setIsDeleteModalOpen] = useState(false);

  // Alert state
  const [alertConfig, setAlertConfig] = useState<{
    title: string;
    message: string;
    type: 'error' | 'success';
  } | null>(null);

  const fetchSite = useCallback(async () => {
    if (!id) return;
    try {
      setIsLoading(true);
      const data = await createSiteService().getSite(id);
      setSite(data);
    } catch (error: unknown) {
      console.error('Failed to fetch site', error);
    } finally {
      setIsLoading(false);
    }
  }, [id]);

  const fetchUsers = useCallback(async () => {
    try {
      const data = await createUserService().getAllUsers();
      setUsers(data);
    } catch (error: unknown) {
      console.error('Failed to fetch users', error);
    }
  }, []);

  useEffect(() => {
    fetchSite();
    fetchUsers();
  }, [id, fetchSite, fetchUsers]);

  const handleAssignUser = async (userId: string) => {
    if (!site) return;
    try {
      await createSiteService().assignUser({ userId, siteId: site.id });
      await fetchSite(); // Refresh to show new editor
    } catch (error: unknown) {
      setAlertConfig({
        title: 'Assignment Failed',
        message: error instanceof Error ? error.message : 'Failed to assign user',
        type: 'error',
      });
      throw error;
    }
  };

  const handleConfirmRemoval = async () => {
    if (!site || !userToRemove) return;
    try {
      await createSiteService().unassignUser(site.id, userToRemove);
      await fetchSite();
      setUserToRemove(null);
    } catch (error: unknown) {
      setAlertConfig({
        title: 'Action Failed',
        message: error instanceof Error ? error.message : 'Failed to remove user',
        type: 'error',
      });
    }
  };

  const handleUpdateSite = async (data: CreateSiteRequest) => {
    if (!site) return;
    try {
      await createSiteService().updateSite(site.id, data);
      setIsEditing(false);
      await fetchSite();
    } catch (error: unknown) {
      setAlertConfig({
        title: 'Update Failed',
        message: error instanceof Error ? error.message : 'Failed to update site',
        type: 'error',
      });
    }
  };

  const handleConfirmDelete = async () => {
    if (!site) return;
    try {
      await createSiteService().deleteSite(site.id);
      navigate('/admin/sites');
    } catch (error) {
      setAlertConfig({
        title: 'Deletion Failed',
        message: error instanceof Error ? error.message : 'Failed to delete site',
        type: 'error',
      });
    }
  };

  const availableUsers = users.filter(
    (user) => !site?.editors?.some((editor) => editor.id === user.id)
  );

  if (isLoading) return <div className="p-8">Loading...</div>;
  if (!site) return <div className="p-8">Site not found</div>;

  return (
    <div className="space-y-6 animate-in fade-in duration-500">
      <div className="flex items-center justify-between">
        <div>
          <Button
            variant="ghost"
            onClick={() => navigate('/admin/sites')}
            className="mb-2 p-0 h-auto text-slate-500"
          >
            ← Back to Sites
          </Button>
          <h1 className="text-3xl font-serif font-semibold text-slate-900">{site.name}</h1>
          <a
            href={site.url}
            target="_blank"
            rel="noopener noreferrer"
            className="text-primary hover:underline"
          >
            {site.url}
          </a>
        </div>
        <div className="flex gap-2">
          <Button variant="secondary" onClick={() => setIsEditing(true)}>
            Edit Site
          </Button>
          <Button variant="primary" onClick={() => setIsAssignModalOpen(true)}>
            Assign User
          </Button>
          <Button
            variant="secondary"
            onClick={() => setIsDeleteModalOpen(true)}
            className="text-red-600 hover:bg-red-50 hover:text-red-700"
          >
            Delete Site
          </Button>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="lg:col-span-2">
          <EditorList editors={site.editors} onRemove={setUserToRemove} />
        </div>

        <div>
          <SiteDetailsCard site={site} />
        </div>
      </div>

      <AssignUserModal
        isOpen={isAssignModalOpen}
        onClose={() => setIsAssignModalOpen(false)}
        siteName={site.name}
        onAssign={handleAssignUser}
        users={availableUsers}
      />

      <ConfirmActionModal
        isOpen={!!userToRemove}
        onClose={() => setUserToRemove(null)}
        onConfirm={handleConfirmRemoval}
        title="Remove Editor"
        message="Are you sure you want to remove this editor? They will no longer have access to this site."
        confirmLabel="Remove"
        variant="danger"
      />

      <ConfirmActionModal
        isOpen={isDeleteModalOpen}
        onClose={() => setIsDeleteModalOpen(false)}
        onConfirm={handleConfirmDelete}
        title="Delete Site"
        message={`Are you sure you want to delete "${site.name}"? This action cannot be undone.`}
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

      {isEditing && (
        <div className="fixed inset-0 bg-gray-600/50 backdrop-blur-sm overflow-y-auto h-full w-full flex items-center justify-center z-50">
          <div className="relative p-8 border w-full max-w-md shadow-xl rounded-2xl bg-white">
            <h3 className="text-xl font-bold mb-4">Edit Site</h3>
            <SiteForm
              initialData={{ name: site.name, url: site.url, type: site.type }}
              onSubmit={handleUpdateSite}
              onCancel={() => setIsEditing(false)}
              submitLabel="Save Changes"
            />
          </div>
        </div>
      )}
    </div>
  );
};
