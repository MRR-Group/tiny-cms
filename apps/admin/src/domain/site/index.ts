import { SiteService } from './siteService';

// Assuming Vite env var or hardcoded for now, matching authService pattern if visible
// authService didn't show config usage, but likely uses VITE_API_URL
export const createSiteService = () => new SiteService(import.meta.env.VITE_API_URL || 'http://localhost:8080');
export * from './types';
export * from './siteService';
