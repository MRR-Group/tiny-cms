import {
  AssignUserRequest,
  CreateSiteRequest,
  CreateSiteSectionRequest,
  Site,
  SiteSection,
  UpdateSiteSectionRequest,
  UpdateSiteRequest,
} from './types';

export class SiteService {
  constructor(private readonly baseUrl: string) {}

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

      const errorMessage =
        error.error?.message ||
        (typeof error.error === 'string' ? error.error : null) ||
        'Request failed';

      throw new Error(errorMessage);
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

  async updateSite(id: string, data: UpdateSiteRequest): Promise<void> {
    await this.request(`/admin/sites/${id}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  }

  async deleteSite(id: string): Promise<void> {
    await this.request(`/admin/sites/${id}`, {
      method: 'DELETE',
    });
  }

  async getSites(): Promise<Site[]> {
    return this.request<Site[]>('/admin/sites', {
      method: 'GET',
    });
  }

  async getSite(id: string): Promise<Site> {
    return this.request<Site>(`/admin/sites/${id}`, {
      method: 'GET',
    });
  }

  async assignUser(data: AssignUserRequest): Promise<void> {
    await this.request('/admin/sites/assign', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  async unassignUser(siteId: string, userId: string): Promise<void> {
    await this.request(`/admin/sites/${siteId}/users/${userId}`, {
      method: 'DELETE',
    });
  }

  async getAssignedSites(): Promise<Site[]> {
    return this.request<Site[]>('/sites', {
      method: 'GET',
    });
  }

  async getSections(siteId: string): Promise<SiteSection[]> {
    return this.request<SiteSection[]>(`/sites/${siteId}/sections`, {
      method: 'GET',
    });
  }

  async createSection(siteId: string, data: CreateSiteSectionRequest): Promise<SiteSection> {
    return this.request<SiteSection>(`/admin/sites/${siteId}/sections`, {
      method: 'POST',
      body: JSON.stringify({ ...data, siteId }),
    });
  }

  async updateSection(
    siteId: string,
    sectionId: string,
    data: UpdateSiteSectionRequest
  ): Promise<SiteSection> {
    return this.request<SiteSection>(`/admin/sites/${siteId}/sections/${sectionId}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  }

  async deleteSection(siteId: string, sectionId: string): Promise<void> {
    await this.request(`/admin/sites/${siteId}/sections/${sectionId}`, {
      method: 'DELETE',
    });
  }

  async getSectionItems(siteId: string, sectionId: string): Promise<Array<Record<string, unknown>>> {
    return this.request<Array<Record<string, unknown>>>(`/sites/${siteId}/sections/${sectionId}/items`, {
      method: 'GET',
    });
  }

  async createSectionItem(
    siteId: string,
    sectionId: string,
    data: Record<string, unknown>
  ): Promise<Record<string, unknown>> {
    return this.request<Record<string, unknown>>(`/sites/${siteId}/sections/${sectionId}/items`, {
      method: 'POST',
      body: JSON.stringify({ data }),
    });
  }

  async updateSectionItem(
    siteId: string,
    sectionId: string,
    itemId: string,
    data: Record<string, unknown>
  ): Promise<Record<string, unknown>> {
    return this.request<Record<string, unknown>>(`/sites/${siteId}/sections/${sectionId}/items/${itemId}`, {
      method: 'PUT',
      body: JSON.stringify({ data }),
    });
  }

  async deleteSectionItem(siteId: string, sectionId: string, itemId: string): Promise<void> {
    await this.request(`/sites/${siteId}/sections/${sectionId}/items/${itemId}`, {
      method: 'DELETE',
    });
  }

  async reorderSections(siteId: string, sectionIds: string[]): Promise<void> {
    await this.request(`/sites/${siteId}/sections/order`, {
      method: 'PUT',
      body: JSON.stringify({ siteId, sectionIds }),
    });
  }
}

export function createSiteService() {
  return new SiteService(import.meta.env.VITE_API_URL || 'http://localhost:8080');
}
