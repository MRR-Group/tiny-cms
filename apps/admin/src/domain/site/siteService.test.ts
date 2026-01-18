
import { SiteService } from './siteService';
import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';

describe('SiteService', () => {
    const baseUrl = 'http://api.test';
    let service: SiteService;
    const fetchMock = vi.fn();

    beforeEach(() => {
        vi.stubGlobal('fetch', fetchMock);
        service = new SiteService(baseUrl);
        localStorage.clear();
    });

    afterEach(() => {
        vi.unstubAllGlobals();
    });

    it('getSites fetches list of sites', async () => {
        const sites = [{ id: '1', name: 'Site 1', url: 'u', type: 'static' }];
        const response = {
            ok: true,
            status: 200,
            text: async () => JSON.stringify(sites),
            json: async () => sites,
        };
        fetchMock.mockResolvedValue(response);

        await service.getSites();

        const callArgs = fetchMock.mock.calls[0];
        const headers = callArgs[1].headers;
        expect(headers['Content-Type']).toBe('application/json');
        expect(headers['Authorization']).toBeUndefined();
    });

    it('createSite sends post request', async () => {
        const data = { name: 'New Site', url: 'http://example.com', type: 'static' as const };
        const responseData = { id: '1' };
        const response = {
            ok: true,
            status: 201,
            text: async () => JSON.stringify(responseData),
            json: async () => responseData,
        };
        fetchMock.mockResolvedValue(response);

        const result = await service.createSite(data);

        expect(fetchMock).toHaveBeenCalledWith(`${baseUrl}/admin/sites`, expect.objectContaining({
            method: 'POST',
            body: JSON.stringify(data),
        }));
        expect(result).toEqual(responseData);
    });

    it('assignUser sends post request', async () => {
        const data = { userId: 'uid', siteId: 'sid' };
        const response = {
            ok: true,
            status: 204,
            text: async () => '',
            json: async () => ({}),
        };
        fetchMock.mockResolvedValue(response);

        await service.assignUser(data);

        expect(fetchMock).toHaveBeenCalledWith(`${baseUrl}/admin/sites/assign`, expect.objectContaining({
            method: 'POST',
            body: JSON.stringify(data),
        }));
    });

    it('sends auth token if present', async () => {
        localStorage.setItem('authToken', 'token123');
        const sites = [];
        const response = {
            ok: true,
            status: 200,
            text: async () => JSON.stringify(sites),
            json: async () => sites,
        };
        fetchMock.mockResolvedValue(response);

        await service.getSites();

        expect(fetchMock).toHaveBeenCalledWith(expect.any(String), expect.objectContaining({
            headers: expect.objectContaining({
                'Authorization': 'Bearer token123'
            })
        }));
    });

    it('throws error if response not ok', async () => {
        const response = {
            ok: false,
            status: 400,
            json: async () => ({ error: { message: 'Some Error' } }),
        };
        fetchMock.mockResolvedValue(response);

        await expect(service.getSites()).rejects.toThrow('Some Error');
    });
});
