export default {
  mutate: ['src/lib/**/*.ts', 'src/domain/**/*.ts'],
  testRunner: 'vitest',
  reporters: ['html', 'clear-text', 'progress'],
  coverageAnalysis: 'perTest',
  thresholds: {
    high: 80,
    low: 50,
  },
  vitest: {
    configFile: 'vite.config.ts',
  },
};
