import { normalizeTitle } from '../domain/normalizeTitle';

describe('normalizeTitle', () => {
  it('returns Untitled for empty input', () => {
    expect(normalizeTitle('')).toBe('Untitled');
  });

  it('capitalizes each word', () => {
    expect(normalizeTitle('tiny cms admin')).toBe('Tiny Cms Admin');
  });
});
