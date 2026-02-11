export type SiteType = 'static' | 'dynamic';

export interface Site {
  id: string;
  name: string;
  url: string;
  type: SiteType;
  editorCount: number;
  createdAt: string;
  editors?: User[];
  sections?: SiteSection[];
}

export type SiteSectionType = 'text' | 'contact' | 'social' | 'news' | 'product' | 'poem';

export interface SiteSectionContact {
  type: string;
  value: string;
}

export interface SiteSection {
  id: string;
  type: SiteSectionType | string;
  title: string;
  data: Record<string, unknown>;
  position: number;
  createdAt: string;
}

export interface CreateSiteSectionRequest {
  type: SiteSectionType | string;
  title: string;
}

export interface UpdateSiteSectionRequest {
  title: string;
  data: Record<string, unknown>;
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
