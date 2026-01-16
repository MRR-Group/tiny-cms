import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import api from '@/lib/api';

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8080';

describe('api client', () => {
    const fetchMock = vi.fn();

    beforeEach(() => {
        global.fetch = fetchMock;
        fetchMock.mockResolvedValue({
            ok: true,
            status: 200,
            json: async () => ({ success: true }),
        });
    });

    afterEach(() => {
        vi.restoreAllMocks();
    });

    it('performs a GET request with correct url', async () => {
        await api.get('/test');

        expect(fetchMock).toHaveBeenCalledWith(`${API_BASE_URL}/test`, expect.objectContaining({
            method: 'GET',
            headers: expect.objectContaining({
                'Content-Type': 'application/json',
            }),
        }));
    });

    it('performs a GET request with query params', async () => {
        await api.get('/test', { params: { foo: 'bar', baz: 'qux' } });

        expect(fetchMock).toHaveBeenCalledWith(
            `${API_BASE_URL}/test?foo=bar&baz=qux`,
            expect.objectContaining({ method: 'GET' })
        );
    });

    it('performs a POST request with body', async () => {
        const body = { key: 'value' };
        await api.post('/test', body);

        expect(fetchMock).toHaveBeenCalledWith(`${API_BASE_URL}/test`, expect.objectContaining({
            method: 'POST',
            body: JSON.stringify(body),
        }));
    });

    it('performs a PUT request with body', async () => {
        const body = { updated: true };
        await api.put('/test', body);

        expect(fetchMock).toHaveBeenCalledWith(`${API_BASE_URL}/test`, expect.objectContaining({
            method: 'PUT',
            body: JSON.stringify(body),
        }));
    });

    it('performs a DELETE request', async () => {
        await api.delete('/test');

        expect(fetchMock).toHaveBeenCalledWith(`${API_BASE_URL}/test`, expect.objectContaining({
            method: 'DELETE',
        }));
    });

    it('merges custom headers', async () => {
        await api.get('/test', { headers: { 'X-Custom': '123' } });

        expect(fetchMock).toHaveBeenCalledWith(expect.any(String), expect.objectContaining({
            headers: expect.objectContaining({
                'Content-Type': 'application/json',
                'X-Custom': '123',
            }),
        }));
    });

    it('returns standardized response format', async () => {
        const responseData = { id: 1 };
        fetchMock.mockResolvedValueOnce({
            ok: true,
            status: 201,
            json: async () => responseData,
        });

        const result = await api.post('/create', {});

        expect(result).toEqual({
            data: responseData,
            status: 201,
            ok: true,
        });
    });

    it('calls health endpoint correctly', async () => {
        await api.health();

        expect(fetchMock).toHaveBeenCalledWith(`${API_BASE_URL}/health`, expect.objectContaining({
            method: 'GET'
        }));
    });
});
