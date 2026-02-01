import { SiteService } from './siteService';

export function createSiteService() {
  return new SiteService(import.meta.env.VITE_API_URL || 'http://localhost:8080');
}
export * from './siteService';
