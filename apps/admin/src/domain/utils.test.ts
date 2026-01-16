import { describe, it, expect } from 'vitest';
import { capitalize, formatNumber, truncate, isValidEmail, slugify, clamp } from '@/domain/utils';

describe('capitalize', () => {
    it('capitalizes first letter', () => {
        expect(capitalize('hello')).toBe('Hello');
    });

    it('returns empty string for empty input', () => {
        expect(capitalize('')).toBe('');
    });

    it('handles single character', () => {
        expect(capitalize('a')).toBe('A');
    });
});

describe('formatNumber', () => {
    it('formats thousands', () => {
        expect(formatNumber(1000)).toBe('1,000');
    });

    it('formats millions', () => {
        expect(formatNumber(1000000)).toBe('1,000,000');
    });

    it('handles zero', () => {
        expect(formatNumber(0)).toBe('0');
    });
});

describe('truncate', () => {
    it('does not truncate short strings', () => {
        expect(truncate('hello', 10)).toBe('hello');
    });

    it('truncates long strings', () => {
        expect(truncate('hello world', 5)).toBe('hello...');
    });

    it('handles exact length', () => {
        expect(truncate('hello', 5)).toBe('hello');
    });
});

describe('isValidEmail', () => {
    it('validates correct email', () => {
        expect(isValidEmail('test@example.com')).toBe(true);
    });

    it('rejects invalid email', () => {
        expect(isValidEmail('invalid')).toBe(false);
    });

    it('rejects email without domain', () => {
        expect(isValidEmail('test@')).toBe(false);
    });

    it('rejects email with trailing characters', () => {
        expect(isValidEmail('test@example.com invalid')).toBe(false);
    });

    it('rejects email with leading characters', () => {
        expect(isValidEmail('invalid test@example.com')).toBe(false);
    });
});

describe('slugify', () => {
    it('converts to lowercase', () => {
        expect(slugify('Hello World')).toBe('hello-world');
    });

    it('removes special characters', () => {
        expect(slugify('Hello! World?')).toBe('hello-world');
    });

    it('handles multiple spaces', () => {
        expect(slugify('hello   world')).toBe('hello-world');
    });

    it('removes leading and trailing dashes', () => {
        expect(slugify('---hello world---')).toBe('hello-world');
    });

    it('handles dashes only', () => {
        expect(slugify('---')).toBe('');
    });
});

describe('clamp', () => {
    it('returns value if within range', () => {
        expect(clamp(5, 0, 10)).toBe(5);
    });

    it('returns min if below range', () => {
        expect(clamp(-5, 0, 10)).toBe(0);
    });

    it('returns max if above range', () => {
        expect(clamp(15, 0, 10)).toBe(10);
    });
});
