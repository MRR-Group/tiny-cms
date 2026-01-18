import { SiteService } from './siteService';
import { Site } from './types';
import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';

describe('SiteService', () => {
  const baseUrl = 'http://api.test';
  let service: SiteService;
  const fetchMock = vi.fn();

  beforeEach(() => {
    fetchMock.mockClear();
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
    expect(callArgs[0]).toBe(`${baseUrl}/admin/sites`);
    expect(callArgs[1].method).toBe('GET');
    expect(callArgs[1].headers['Content-Type']).toBe('application/json');
    expect(callArgs[1].headers['Authorization']).toBeUndefined();
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

    const callArgs = fetchMock.mock.calls[0];
    expect(callArgs[0]).toBe(`${baseUrl}/admin/sites`);
    expect(callArgs[1].method).toBe('POST');
    expect(callArgs[1].body).toBe(JSON.stringify(data));
    expect(result).toEqual(responseData);
  });

  it('assignUser returns early on 204', async () => {
    const data = { userId: 'uid', siteId: 'sid' };
    const textSpy = vi.fn();
    const response = {
      ok: true,
      status: 204,
      text: textSpy,
      json: async () => ({}),
    };
    fetchMock.mockResolvedValue(response);

    await service.assignUser(data);

    const callArgs = fetchMock.mock.calls[0];
    expect(callArgs[0]).toBe(`${baseUrl}/admin/sites/assign`);
    expect(callArgs[1].method).toBe('POST');
    expect(callArgs[1].body).toBe(JSON.stringify(data));
    expect(textSpy).not.toHaveBeenCalled();
  });

  it('sends auth token if present', async () => {
    localStorage.setItem('authToken', 'token123');
    const sites: Site[] = [];
    const response = {
      ok: true,
      status: 200,
      text: async () => JSON.stringify(sites),
      json: async () => sites,
    };
    fetchMock.mockResolvedValue(response);

    await service.getSites();

    const callArgs = fetchMock.mock.calls[0];
    expect(callArgs[1].headers['Authorization']).toBe('Bearer token123');
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

  it('throws "Request failed" if error body is empty', async () => {
    const response = {
      ok: false,
      status: 500,
      json: async () => ({}),
    };
    fetchMock.mockResolvedValue(response);

    await expect(service.getSites()).rejects.toThrow('Request failed');
  });

  it('handles json parsing error gracefully', async () => {
    const response = {
      ok: false,
      status: 500,
      json: async () => {
        throw new Error('Invalid JSON');
      },
    };
    fetchMock.mockResolvedValue(response);

    await expect(service.getSites()).rejects.toThrow('An error occurred');
  });

  it('getAssignedSites fetches list of sites', async () => {
    const sites = [{ id: '1', name: 'Site 1', url: 'u', type: 'static' }];
    const response = {
      ok: true,
      status: 200,
      text: async () => JSON.stringify(sites),
      json: async () => sites,
    };
    fetchMock.mockResolvedValue(response);

    const result = await service.getAssignedSites();

    expect(fetchMock).toHaveBeenCalledWith(
      `${baseUrl}/sites`,
      expect.objectContaining({
        method: 'GET',
      })
    );
    expect(result).toEqual(sites);
  });
});
