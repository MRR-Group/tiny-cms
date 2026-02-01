import { describe, it, expect } from 'vitest';
import { createSiteService } from './index';
import { SiteService } from './siteService';

describe('Site Domain Factory', () => {
  it('createSiteService returns an instance of SiteService', () => {
    const service = createSiteService();
    expect(service).toBeInstanceOf(SiteService);
  });
});
