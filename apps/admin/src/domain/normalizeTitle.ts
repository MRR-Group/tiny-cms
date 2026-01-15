export const normalizeTitle = (value: string): string => {
  if (!value.trim()) {
    return 'Untitled';
  }

  return value
    .split(' ')
    .filter(Boolean)
    .map((part) => part[0].toUpperCase() + part.slice(1))
    .join(' ');
};
