import { AssignUserRequest, CreateSiteRequest, Site } from './types';

export class SiteService {
    constructor(private readonly baseUrl: string) { }

    private async request<T>(endpoint: string, options: RequestInit): Promise<T> {
        const token = localStorage.getItem('authToken');

        const headers: HeadersInit = {
            'Content-Type': 'application/json',
        };

        if (token) {
            Object.assign(headers, { Authorization: `Bearer ${token}` });
        }

        const response = await fetch(`${this.baseUrl}${endpoint}`, {
            ...options,
            headers,
        });

        if (!response.ok) {
            const error = await response.json().catch(() => ({
                error: { message: 'An error occurred' },
            }));
            throw new Error(error.error?.message || 'Request failed');
        }

        // Some 204 responses might have no content
        if (response.status === 204) {
            return {} as T;
        }

        const text = await response.text();
        return text ? JSON.parse(text) : ({} as T);
    }

    async createSite(data: CreateSiteRequest): Promise<{ id: string }> {
        return this.request<{ id: string }>('/admin/sites', {
            method: 'POST',
            body: JSON.stringify(data),
        });
    }

    async getSites(): Promise<Site[]> {
        return this.request<Site[]>('/admin/sites', {
            method: 'GET',
        });
    }

    async assignUser(data: AssignUserRequest): Promise<void> {
        await this.request('/admin/sites/assign', {
            method: 'POST',
            body: JSON.stringify(data),
        });
    }

    async getAssignedSites(): Promise<Site[]> {
        return this.request<Site[]>('/sites', {
            method: 'GET',
        });
    }
}
