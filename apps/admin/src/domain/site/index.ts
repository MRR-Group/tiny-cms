import { SiteService } from './siteService';

// Assuming Vite env var or hardcoded for now, matching authService pattern if visible
// authService didn't show config usage, but likely uses VITE_API_URL
const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8080';

export const siteService = new SiteService(API_URL);
export * from './types';
export * from './siteService';
