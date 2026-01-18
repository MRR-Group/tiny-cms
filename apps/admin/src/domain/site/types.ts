export type SiteType = 'static' | 'dynamic';

export interface Site {
    id: string;
    name: string;
    url: string;
    type: SiteType;
    createdAt: string;
}

export interface CreateSiteRequest {
    name: string;
    url: string;
    type: SiteType;
}

export interface AssignUserRequest {
    userId: string;
    siteId: string;
}
