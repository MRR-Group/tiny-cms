import React, { useEffect, useState, useCallback } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import {
  CreateSiteRequest,
  CreateSiteSectionRequest,
  Site,
  SiteSection,
  User,
} from '@/domain/site/types';
import { createSiteService } from '@/domain/site';
import { createUserService } from '@/domain/user/userService';
import { Button } from '@/components/Button/Button';
import { Input } from '@/components/Input/Input';
import { Select } from '@/components/Select/Select';
import { AssignUserModal } from '@/components/Site/AssignUserModal';
import { SiteForm } from '@/components/Site/SiteForm';
import { EditorList } from '@/components/Site/EditorList';
import { SiteDetailsCard } from '@/components/Site/SiteDetailsCard';
import { ConfirmActionModal } from '@/components/Modal/ConfirmActionModal';
import { AlertModal } from '@/components/Modal/AlertModal';
import { createAuthService } from '@/domain/auth';

interface SectionFormErrors {
  title?: string;
}

export const SiteDetailsPage: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const [site, setSite] = useState<Site | null>(null);
  const [sections, setSections] = useState<SiteSection[]>([]);
  const [users, setUsers] = useState<User[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isSectionSaving, setIsSectionSaving] = useState(false);
  const [sectionErrors, setSectionErrors] = useState<SectionFormErrors>({});

  const [sectionType, setSectionType] = useState('text');
  const [sectionTitle, setSectionTitle] = useState('');
  const [sectionToDeleteId, setSectionToDeleteId] = useState<string | null>(null);

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

  const isAdmin = createAuthService().getUserRole() === 'admin';

  const fetchSite = useCallback(async () => {
    if (!id) return;
    try {
      setIsLoading(true);
      const data = isAdmin
        ? await createSiteService().getSite(id)
        : await createSiteService().getAssignedSites().then((sites) => {
            const assigned = sites.find((siteItem) => siteItem.id === id);
            if (!assigned) {
              throw new Error('Site not found');
            }

            return assigned;
          });

      setSite(data);
      setSections(data.sections ?? []);
    } catch (error: unknown) {
      console.error('Failed to fetch site', error);
      setSite(null);
    } finally {
      setIsLoading(false);
    }
  }, [id, isAdmin]);

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
    if (isAdmin) {
      fetchUsers();
    }
  }, [id, fetchSite, fetchUsers, isAdmin]);

  useEffect(() => {
    const fetchSectionsForEditor = async () => {
      if (!id || !site || isAdmin) return;

      try {
        const data = await createSiteService().getSections(id);
        setSections(data);
      } catch (error: unknown) {
        console.error('Failed to fetch sections', error);
        setSections([]);
      }
    };

    void fetchSectionsForEditor();
  }, [id, site, isAdmin]);

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

  const resetSectionForm = () => {
    setSectionType('text');
    setSectionTitle('');
    setSectionErrors({});
  };

  const validateSectionForm = (): boolean => {
    const nextErrors: SectionFormErrors = {};

    if (sectionTitle.trim() === '') {
      nextErrors.title = 'Title is required';
    }

    setSectionErrors(nextErrors);

    return !nextErrors.title;
  };

  const handleCreateSection = async (event: React.FormEvent) => {
    event.preventDefault();

    if (!site || !isAdmin) return;
    if (!validateSectionForm()) return;

    const payload: CreateSiteSectionRequest = {
      type: sectionType,
      title: sectionTitle,
    };

    try {
      setIsSectionSaving(true);
      await createSiteService().createSection(site.id, payload);
      resetSectionForm();
      await fetchSite();
    } catch (error: unknown) {
      const message = error instanceof Error ? error.message : 'Failed to save section';

      if (message.includes('Section title')) {
        setSectionErrors((current) => ({ ...current, title: 'Title is required' }));
      } else {
        setAlertConfig({
          title: 'Section Save Failed',
          message,
          type: 'error',
        });
      }
    } finally {
      setIsSectionSaving(false);
    }
  };

  const handleDeleteSection = async () => {
    if (!site || !sectionToDeleteId || !isAdmin) {
      return;
    }

    try {
      await createSiteService().deleteSection(site.id, sectionToDeleteId);
      await fetchSite();
      setSectionToDeleteId(null);
    } catch (error: unknown) {
      setAlertConfig({
        title: 'Section Delete Failed',
        message: error instanceof Error ? error.message : 'Failed to delete section',
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
            onClick={() => navigate(isAdmin ? '/admin/sites' : '/')}
            className="mb-2 p-0 h-auto text-slate-500"
          >
            ← {isAdmin ? 'Back to Sites' : 'Back to Dashboard'}
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
        {isAdmin && (
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
        )}
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {isAdmin && (
          <div className="lg:col-span-2">
            <EditorList editors={site.editors} onRemove={setUserToRemove} />
          </div>
        )}

        <div className={isAdmin ? '' : 'lg:col-span-3'}>
          <SiteDetailsCard site={site} />
        </div>
      </div>

      <div className="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div className="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm">
          <h2 className="text-xl font-bold text-slate-900 mb-1">Create Section</h2>
          <p className="text-sm text-slate-500 mb-6">
            Create only type and title. Add real data later in section editor.
          </p>

          {!isAdmin ? (
            <div className="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
              Only admin can manage sections.
            </div>
          ) : (
            <form onSubmit={handleCreateSection} className="space-y-4">
              <Select
                id="sectionType"
                label="Section Type"
                value={sectionType}
                onChange={(e) => setSectionType(e.target.value)}
                options={[
                  { value: 'text', label: 'Text' },
                  { value: 'contact', label: 'Contact' },
                  { value: 'image', label: 'Image' },
                  { value: 'news', label: 'News' },
                  { value: 'social', label: 'Social' },
                  { value: 'product', label: 'Product' },
                  { value: 'poem', label: 'Poem' },
                ]}
              />
              <Input
                id="sectionTitle"
                label="Title"
                value={sectionTitle}
                onChange={(e) => setSectionTitle(e.target.value)}
                error={sectionErrors.title}
                required
              />
              <Button type="submit" variant="primary" disabled={isSectionSaving}>
                {isSectionSaving ? 'Saving...' : 'Create Section'}
              </Button>
            </form>
          )}
        </div>

        <div className="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm">
          <h2 className="text-xl font-bold text-slate-900 mb-4">Sections</h2>

          {sections.length === 0 ? (
            <p className="text-sm text-slate-500">No sections yet. Add the first section.</p>
          ) : (
            <div className="space-y-3">
              {sections.map((section) => (
                <div key={section.id} className="border border-slate-200 rounded-xl p-4 bg-white">
                  <div className="flex items-start justify-between gap-4">
                    <div>
                      <p className="text-xs font-semibold uppercase tracking-wider text-slate-500">
                        {section.type}
                      </p>
                      <p className="text-sm font-semibold text-slate-900">{section.title}</p>
                    </div>
                    <div className="flex items-center gap-2">
                      <Button
                        type="button"
                        variant="secondary"
                        size="sm"
                        onClick={() => navigate(`/admin/sites/${site.id}/sections/${section.id}`)}
                      >
                        {isAdmin ? 'Edit' : 'Content'}
                      </Button>
                      {isAdmin && (
                        <Button type="button" variant="danger" size="sm" onClick={() => setSectionToDeleteId(section.id)}>
                          Delete
                        </Button>
                      )}
                    </div>
                  </div>

                </div>
              ))}
            </div>
          )}
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

      <ConfirmActionModal
        isOpen={!!sectionToDeleteId}
        onClose={() => setSectionToDeleteId(null)}
        onConfirm={handleDeleteSection}
        title="Delete Section"
        message="Are you sure you want to delete this section?"
        confirmLabel="Delete Section"
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
