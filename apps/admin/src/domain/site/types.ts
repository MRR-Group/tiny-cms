export type SiteType = 'static' | 'dynamic';

export interface Site {
  id: string;
  name: string;
  url: string;
  type: SiteType;
  editorCount: number;
  createdAt: string;
}

export interface CreateSiteRequest {
  name: string;
  url: string;
  type: SiteType;
}

export interface UpdateSiteRequest extends CreateSiteRequest {}

export interface AssignUserRequest {
  userId: string;
  siteId: string;
}

export interface User {
  id: string;
  email: string;
  role: string;
}
