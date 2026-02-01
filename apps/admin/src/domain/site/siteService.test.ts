import { describe, it, expect, vi, beforeEach } from 'vitest';
import { SiteService, createSiteService } from './siteService';

const mockFetch = vi.fn();
global.fetch = mockFetch;

describe('SiteService', () => {
    let siteService: SiteService;

    const expectRequest = (url: string, method: string) => {
        expect(mockFetch).toHaveBeenCalledWith(
            `http://api.com${url}`,
            expect.objectContaining({
                method,
                headers: expect.objectContaining({ 'Content-Type': 'application/json' }),
            })
        );
    };

    beforeEach(() => {
        siteService = new SiteService('http://api.com');
        mockFetch.mockClear();
        localStorage.clear();
    });

    it('getSites returns list of sites', async () => {
        const mockSites = [{ id: '1', name: 'Site 1' }];
        mockFetch.mockResolvedValue({
            ok: true,
            status: 200,
            text: async () => JSON.stringify(mockSites),
        });

        const sites = await siteService.getSites();

        expectRequest('/admin/sites', 'GET');
        expect(sites).toEqual(mockSites);
    });

    it('getSite returns detailed site data', async () => {
        const mockSite = { id: '1', name: 'Site 1', editors: [] };
        mockFetch.mockResolvedValue({
            ok: true,
            status: 200,
            text: async () => JSON.stringify(mockSite),
        });

        const site = await siteService.getSite('1');

        expectRequest('/admin/sites/1', 'GET');
        expect(site).toEqual(mockSite);
    });

    it('createSite sends POST request', async () => {
        mockFetch.mockResolvedValue({
            ok: true,
            status: 201,
            text: async () => JSON.stringify({ id: '2' }),
        });

        const data = { name: 'New Site', url: 'http://new.com', type: 'static' as const };
        await siteService.createSite(data);

        expectRequest('/admin/sites', 'POST');
        expect(mockFetch).toHaveBeenCalledWith(
            expect.any(String),
            expect.objectContaining({
                body: JSON.stringify(data),
            })
        );
    });

    it('updateSite sends PUT request', async () => {
        mockFetch.mockResolvedValue({
            ok: true,
            status: 204,
            text: async () => '',
        });

        const data = { name: 'Updated Site', url: 'http://site.com', type: 'dynamic' as const };
        await siteService.updateSite('1', data);

        expectRequest('/admin/sites/1', 'PUT');
        expect(mockFetch).toHaveBeenCalledWith(
            expect.any(String),
            expect.objectContaining({
                body: JSON.stringify(data),
            })
        );
    });

    it('deleteSite sends DELETE request', async () => {
        mockFetch.mockResolvedValue({
            ok: true,
            status: 204,
            text: async () => '',
        });

        await siteService.deleteSite('1');

        expectRequest('/admin/sites/1', 'DELETE');
    });

    it('assignUser sends POST request', async () => {
        mockFetch.mockResolvedValue({
            ok: true,
            status: 204,
            text: async () => '',
        });

        const data = { userId: 'u1', siteId: 's1' };
        await siteService.assignUser(data);

        expectRequest('/admin/sites/assign', 'POST');
    });

    it('unassignUser sends DELETE request', async () => {
        mockFetch.mockResolvedValue({
            ok: true,
            status: 204,
            text: async () => '',
        });

        await siteService.unassignUser('s1', 'u1');

        expectRequest('/admin/sites/s1/users/u1', 'DELETE');
    });

    it('getAssignedSites returns sites for current user', async () => {
        mockFetch.mockResolvedValue({
            ok: true,
            status: 200,
            text: async () => '[]',
        });

        await siteService.getAssignedSites();

        expectRequest('/sites', 'GET');
    });

    it('handles 204 No Content response explicitly', async () => {
        mockFetch.mockResolvedValue({
            ok: true,
            status: 204,
            text: async () => 'should be ignored',
        });

        const result = await siteService.getSites();
        expect(result).toEqual({});
    });

    it('sends Authorization header if token exists', async () => {
        localStorage.setItem('authToken', 'test-token');
        mockFetch.mockResolvedValue({
            ok: true,
            status: 200,
            text: async () => '[]',
        });

        await siteService.getSites();

        expect(mockFetch).toHaveBeenCalledWith(
            expect.any(String),
            expect.objectContaining({
                headers: expect.objectContaining({
                    Authorization: 'Bearer test-token',
                    'Content-Type': 'application/json'
                })
            })
        );
    });

    it('throws error with message from response if available', async () => {
        mockFetch.mockResolvedValue({
            ok: false,
            status: 400,
            json: async () => ({ error: { message: 'Specific Error' } }),
        });

        await expect(siteService.getSites()).rejects.toThrow('Specific Error');
    });

    it('throws generic error if response json fails', async () => {
        mockFetch.mockResolvedValue({
            ok: false,
            status: 500,
            json: async () => { throw new Error('JSON Error'); },
        });

        await expect(siteService.getSites()).rejects.toThrow('An error occurred');
    });

    it('handles error scenario where error object has no message', async () => {
        mockFetch.mockResolvedValue({
            ok: false,
            status: 400,
            json: async () => ({ error: 'Raw error string' }),
        });

        await expect(siteService.getSites()).rejects.toThrow('Raw error string');
    });

    it('handles 200 with empty text', async () => {
        mockFetch.mockResolvedValue({
            ok: true,
            status: 200,
            text: async () => '',
        });

        const result = await siteService.getSites();
        expect(result).toEqual({});
    });

    it('does not send Authorization header when no token', async () => {
        mockFetch.mockResolvedValue({
            ok: true,
            status: 200,
            text: async () => '[]',
        });

        await siteService.getSites();
        
        const callArgs = mockFetch.mock.calls[0][1];
        expect(callArgs.headers).not.toHaveProperty('Authorization');
        expect(callArgs.headers).toHaveProperty('Content-Type', 'application/json');
    });

    it('throws error when error.error is undefined', async () => {
        mockFetch.mockResolvedValue({
            ok: false,
            status: 400,
            json: async () => ({}),
        });

        await expect(siteService.getSites()).rejects.toThrow('Request failed');
    });

    it('throws error when error.error.message is undefined but error.error exists', async () => {
        mockFetch.mockResolvedValue({
            ok: false,
            status: 400,
            json: async () => ({ error: {} }),
        });

        await expect(siteService.getSites()).rejects.toThrow('Request failed');
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
