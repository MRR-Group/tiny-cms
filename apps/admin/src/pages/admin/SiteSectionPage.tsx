import React, { useCallback, useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { Button } from '@/components/Button/Button';
import { Input } from '@/components/Input/Input';
import { ConfirmActionModal } from '@/components/Modal/ConfirmActionModal';
import { AlertModal } from '@/components/Modal/AlertModal';
import { createSiteService } from '@/domain/site';
import { Site, SiteSection } from '@/domain/site/types';
import { createAuthService } from '@/domain/auth';

export const SiteSectionPage: React.FC = () => {
  const { id: siteId, sectionId } = useParams<{ id: string; sectionId: string }>();
  const navigate = useNavigate();

  const [site, setSite] = useState<Site | null>(null);
  const [section, setSection] = useState<SiteSection | null>(null);
  const [items, setItems] = useState<Array<Record<string, unknown>>>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isSavingSection, setIsSavingSection] = useState(false);
  const [isSavingItem, setIsSavingItem] = useState(false);

  const [sectionTitle, setSectionTitle] = useState('');
  const [sectionTitleError, setSectionTitleError] = useState('');

  const [isEditorOpen, setIsEditorOpen] = useState(false);
  const [editingItemId, setEditingItemId] = useState<string | null>(null);
  const [itemJson, setItemJson] = useState('{}');
  const [itemJsonError, setItemJsonError] = useState('');

  const [sectionDeleteOpen, setSectionDeleteOpen] = useState(false);
  const [itemDeleteId, setItemDeleteId] = useState<string | null>(null);

  const [alert, setAlert] = useState<{ title: string; message: string; type: 'error' | 'success' } | null>(null);

  const userRole = createAuthService().getUserRole();
  const isAdmin = userRole === 'admin';
  const currentUserId = createAuthService().getUserId();
  const canEditContent =
    !!currentUserId && !!site?.editors?.some((editor) => editor.id === currentUserId);
  const canViewContent = isAdmin || canEditContent;

  const load = useCallback(async () => {
    if (!siteId || !sectionId) return;

    try {
      setIsLoading(true);
      const siteData = await (isAdmin
        ? createSiteService().getSite(siteId)
        : createSiteService().getAssignedSites().then((sites) => {
            const assigned = sites.find((item) => item.id === siteId);
            if (!assigned) {
              throw new Error('Forbidden');
            }
            return assigned;
          }));

      let found: SiteSection | null = null;
      let sectionItems: Array<Record<string, unknown>> = [];

      if (isAdmin) {
        const siteSections = siteData.sections ?? [];
        found = siteSections.find((item) => item.id === sectionId) ?? null;

        const isAdminAssignedEditor =
          !!currentUserId && !!siteData.editors?.some((editor) => editor.id === currentUserId);

        if (isAdminAssignedEditor) {
          sectionItems = await createSiteService().getSectionItems(siteId, sectionId);
        } else {
          const rawData = found?.data;
          if (rawData && typeof rawData === 'object') {
            const maybeItems = (rawData as { items?: unknown }).items;
            if (Array.isArray(maybeItems)) {
              sectionItems = maybeItems.filter(
                (item): item is Record<string, unknown> =>
                  !!item && typeof item === 'object' && !Array.isArray(item)
              );
            }
          }
        }
      } else {
        const [sections, itemsFromApi] = await Promise.all([
          createSiteService().getSections(siteId),
          createSiteService().getSectionItems(siteId, sectionId),
        ]);
        found = sections.find((item) => item.id === sectionId) ?? null;
        sectionItems = itemsFromApi;
      }

      setSite(siteData);
      setSection(found);
      setSectionTitle(found?.title ?? '');
      setItems(sectionItems);
    } catch (error: unknown) {
      setAlert({
        title: 'Section Load Failed',
        message: error instanceof Error ? error.message : 'Failed to load section',
        type: 'error',
      });
    } finally {
      setIsLoading(false);
    }
  }, [siteId, sectionId, isAdmin, currentUserId]);

  useEffect(() => {
    void load();
  }, [load]);

  const openCreateItem = () => {
    setEditingItemId(null);
    setItemJson('{}');
    setItemJsonError('');
    setIsEditorOpen(true);
  };

  const openEditItem = (item: Record<string, unknown>) => {
    setEditingItemId(typeof item.id === 'string' ? item.id : null);
    setItemJson(JSON.stringify(item, null, 2));
    setItemJsonError('');
    setIsEditorOpen(true);
  };

  const parseItemJson = (): Record<string, unknown> | null => {
    try {
      const parsed = JSON.parse(itemJson);
      if (!parsed || typeof parsed !== 'object' || Array.isArray(parsed)) {
        setItemJsonError('JSON object is required');
        return null;
      }

      return parsed as Record<string, unknown>;
    } catch {
      setItemJsonError('JSON must be valid');
      return null;
    }
  };

  const saveSectionMeta = async () => {
    if (!siteId || !sectionId || !isAdmin) return;

    if (sectionTitle.trim() === '') {
      setSectionTitleError('Title is required');
      return;
    }

    try {
      setIsSavingSection(true);
      setSectionTitleError('');
      await createSiteService().updateSection(siteId, sectionId, { title: sectionTitle.trim(), data: section?.data ?? {} });
      setAlert({ title: 'Section Updated', message: 'Section metadata has been saved.', type: 'success' });
      await load();
    } catch (error: unknown) {
      setAlert({
        title: 'Section Update Failed',
        message: error instanceof Error ? error.message : 'Failed to update section',
        type: 'error',
      });
    } finally {
      setIsSavingSection(false);
    }
  };

  const saveItem = async () => {
    if (!siteId || !sectionId || !canEditContent) return;

    const parsed = parseItemJson();
    if (!parsed) return;

    try {
      setIsSavingItem(true);
      if (editingItemId) {
        await createSiteService().updateSectionItem(siteId, sectionId, editingItemId, parsed);
      } else {
        await createSiteService().createSectionItem(siteId, sectionId, parsed);
      }
      setIsEditorOpen(false);
      await load();
    } catch (error: unknown) {
      setAlert({
        title: 'Item Save Failed',
        message: error instanceof Error ? error.message : 'Failed to save item',
        type: 'error',
      });
    } finally {
      setIsSavingItem(false);
    }
  };

  const deleteItem = async () => {
    if (!siteId || !sectionId || !itemDeleteId || !canEditContent) return;

    try {
      await createSiteService().deleteSectionItem(siteId, sectionId, itemDeleteId);
      setItemDeleteId(null);
      await load();
    } catch (error: unknown) {
      setAlert({
        title: 'Item Delete Failed',
        message: error instanceof Error ? error.message : 'Failed to delete item',
        type: 'error',
      });
      throw error;
    }
  };

  const deleteSection = async () => {
    if (!siteId || !sectionId || !isAdmin) return;

    try {
      await createSiteService().deleteSection(siteId, sectionId);
      navigate(`/admin/sites/${siteId}`);
    } catch (error: unknown) {
      setAlert({
        title: 'Section Delete Failed',
        message: error instanceof Error ? error.message : 'Failed to delete section',
        type: 'error',
      });
      throw error;
    }
  };

  if (isLoading) return <div className="p-8">Loading section...</div>;
  if (!site || !section) return <div className="p-8">Section not found</div>;

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <Button
            variant="ghost"
            onClick={() => navigate(isAdmin ? `/admin/sites/${site.id}` : '/')}
            className="mb-2 p-0 h-auto text-slate-500"
          >
            ← {isAdmin ? 'Back to Site' : 'Back to Dashboard'}
          </Button>
          <h1 className="text-2xl font-serif font-semibold text-slate-900">{section.title}</h1>
          <p className="text-sm text-slate-500 uppercase tracking-wide mt-1">{section.type} section</p>
        </div>
        {isAdmin && (
          <Button variant="danger" onClick={() => setSectionDeleteOpen(true)}>
            Delete Section
          </Button>
        )}
      </div>

      {isAdmin && (
        <div className="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm space-y-3">
          <h2 className="text-lg font-semibold text-slate-900">Section Metadata</h2>
          <Input
            id="section-title"
            label="Title"
            value={sectionTitle}
            error={sectionTitleError}
            onChange={(e) => setSectionTitle(e.target.value)}
          />
          <div className="flex justify-end">
            <Button variant="primary" onClick={saveSectionMeta} disabled={isSavingSection}>
              {isSavingSection ? 'Saving...' : 'Save Section'}
            </Button>
          </div>
        </div>
      )}

      <div className="bg-white border border-slate-100 rounded-2xl p-6 shadow-sm space-y-4">
        <div className="flex items-center justify-between">
          <h2 className="text-lg font-semibold text-slate-900">Section Items</h2>
          {canEditContent && (
            <Button variant="primary" onClick={openCreateItem}>
              + Add Item
            </Button>
          )}
        </div>

        {!canViewContent && (
          <div className="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
            You are not assigned to this site, so this section is read-only.
          </div>
        )}

        {items.length === 0 ? (
          <p className="text-sm text-slate-500">No items in this section yet.</p>
        ) : (
          <div className="space-y-3">
            {items.map((item) => {
              const itemId = typeof item.id === 'string' ? item.id : 'unknown';
              return (
                <div key={itemId} className="border border-slate-200 rounded-xl p-4">
                  <div className="flex items-start justify-between gap-3">
                    <pre className="text-xs text-slate-700 overflow-x-auto whitespace-pre-wrap break-words flex-1">
                      {JSON.stringify(item, null, 2)}
                    </pre>
                    {canEditContent && (
                      <div className="flex gap-2">
                        <Button variant="secondary" size="sm" onClick={() => openEditItem(item)}>
                          Edit
                        </Button>
                        <Button variant="danger" size="sm" onClick={() => setItemDeleteId(itemId)}>
                          Delete
                        </Button>
                      </div>
                    )}
                  </div>
                </div>
              );
            })}
          </div>
        )}
      </div>

      {isEditorOpen && (
        <div className="fixed inset-0 bg-gray-600/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
          <div className="bg-white w-full max-w-2xl rounded-2xl shadow-xl p-6 space-y-4">
            <h3 className="text-lg font-semibold text-slate-900">
              {editingItemId ? 'Edit Item' : 'Create Item'}
            </h3>
            <div>
              <label htmlFor="section-item-json" className="block text-[11px] font-semibold text-slate-400 uppercase tracking-widest ml-1">Item JSON</label>
              <textarea
                id="section-item-json"
                value={itemJson}
                onChange={(e) => {
                  setItemJson(e.target.value);
                  setItemJsonError('');
                }}
                className="w-full min-h-[240px] px-4 py-3 bg-slate-50 border border-slate-100 rounded-xl text-slate-900 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary/10"
              />
              {itemJsonError && <p className="text-[10px] text-red-500 font-medium ml-1">{itemJsonError}</p>}
            </div>
            <div className="flex justify-end gap-2">
              <Button variant="ghost" onClick={() => setIsEditorOpen(false)}>
                Cancel
              </Button>
              <Button variant="primary" onClick={saveItem} disabled={isSavingItem}>
                {isSavingItem ? 'Saving...' : 'Save Item'}
              </Button>
            </div>
          </div>
        </div>
      )}

      <ConfirmActionModal
        isOpen={sectionDeleteOpen}
        onClose={() => setSectionDeleteOpen(false)}
        onConfirm={deleteSection}
        title="Delete Section"
        message="Are you sure you want to delete this section?"
        confirmLabel="Delete Section"
        variant="danger"
      />

      <ConfirmActionModal
        isOpen={!!itemDeleteId}
        onClose={() => setItemDeleteId(null)}
        onConfirm={deleteItem}
        title="Delete Item"
        message="Are you sure you want to delete this item?"
        confirmLabel="Delete Item"
        variant="danger"
      />

      <AlertModal
        isOpen={!!alert}
        onClose={() => setAlert(null)}
        title={alert?.title || ''}
        message={alert?.message || ''}
        type={alert?.type || 'info'}
      />
    </div>
  );
};
