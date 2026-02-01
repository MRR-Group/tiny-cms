import { describe, it, expect, vi, beforeEach } from 'vitest';
import { SiteService, createSiteService } from './siteService';

const mockFetch = vi.fn();
global.fetch = mockFetch;

describe('SiteService', () => {
  let siteService: SiteService;

  beforeEach(() => {
    siteService = new SiteService('http://api.com');
    mockFetch.mockClear();
  });

  it('getSites returns list of sites', async () => {
    const mockSites = [{ id: '1', name: 'Site 1' }];
    mockFetch.mockResolvedValue({
      ok: true,
      text: async () => JSON.stringify(mockSites),
    });

    const sites = await siteService.getSites();

    expect(mockFetch).toHaveBeenCalledWith(
      'http://api.com/admin/sites',
      expect.objectContaining({ method: 'GET' })
    );
    expect(sites).toEqual(mockSites);
  });

  it('getSite returns detailed site data', async () => {
    const mockSite = { id: '1', name: 'Site 1', editors: [] };
    mockFetch.mockResolvedValue({
      ok: true,
      text: async () => JSON.stringify(mockSite),
    });

    const site = await siteService.getSite('1');

    expect(mockFetch).toHaveBeenCalledWith(
      'http://api.com/admin/sites/1',
      expect.objectContaining({ method: 'GET' })
    );
    expect(site).toEqual(mockSite);
  });

  it('createSite sends POST request', async () => {
    mockFetch.mockResolvedValue({
      ok: true,
      text: async () => JSON.stringify({ id: '2' }),
    });

    const data = { name: 'New Site', url: 'http://new.com', type: 'static' as const };
    await siteService.createSite(data);

    expect(mockFetch).toHaveBeenCalledWith(
      'http://api.com/admin/sites',
      expect.objectContaining({
        method: 'POST',
        body: JSON.stringify(data),
      })
    );
  });

  it('updateSite sends PUT request', async () => {
    mockFetch.mockResolvedValue({
      ok: true,
      text: async () => '',
    });

    const data = { name: 'Updated Site', url: 'http://site.com', type: 'dynamic' as const };
    await siteService.updateSite('1', data);

    expect(mockFetch).toHaveBeenCalledWith(
      'http://api.com/admin/sites/1',
      expect.objectContaining({
        method: 'PUT',
        body: JSON.stringify(data),
      })
    );
  });

  it('deleteSite sends DELETE request', async () => {
    mockFetch.mockResolvedValue({
      ok: true,
      text: async () => '',
    });

    await siteService.deleteSite('1');

    expect(mockFetch).toHaveBeenCalledWith(
      'http://api.com/admin/sites/1',
      expect.objectContaining({ method: 'DELETE' })
    );
  });

  it('assignUser sends POST request', async () => {
    mockFetch.mockResolvedValue({
      ok: true,
      text: async () => '',
    });

    await siteService.assignUser({ userId: 'u1', siteId: 's1' });

    expect(mockFetch).toHaveBeenCalledWith(
      'http://api.com/admin/sites/assign',
      expect.objectContaining({
        method: 'POST',
        body: JSON.stringify({ userId: 'u1', siteId: 's1' }),
      })
    );
  });

  it('unassignUser sends DELETE request', async () => {
    mockFetch.mockResolvedValue({
      ok: true,
      text: async () => '',
    });

    await siteService.unassignUser('s1', 'u1');

    expect(mockFetch).toHaveBeenCalledWith(
      'http://api.com/admin/sites/s1/users/u1',
      expect.objectContaining({ method: 'DELETE' })
    );
  });

  it('getAssignedSites returns sites for current user', async () => {
    mockFetch.mockResolvedValue({
      ok: true,
      text: async () => '[]',
    });

    await siteService.getAssignedSites();

    expect(mockFetch).toHaveBeenCalledWith(
      'http://api.com/sites',
      expect.objectContaining({ method: 'GET' })
    );
  });

  describe('factory', () => {
    it('createSiteService uses VITE_API_URL if provided', () => {
      vi.stubEnv('VITE_API_URL', 'http://custom-api.com');
      const instance = createSiteService();
      expect((instance as unknown as { baseUrl: string }).baseUrl).toBe('http://custom-api.com');
      vi.unstubAllEnvs();
    });

    it('createSiteService uses default URL if VITE_API_URL is missing', () => {
      vi.stubEnv('VITE_API_URL', '');
      const instance = createSiteService();
      expect((instance as unknown as { baseUrl: string }).baseUrl).toBe('http://localhost:8080');
      vi.unstubAllEnvs();
    });
  });
});
