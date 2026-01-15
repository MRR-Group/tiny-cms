/** @type {import('@stryker-mutator/api/core').PartialStrykerOptions} */
const config = {
  packageManager: 'pnpm',
  reporters: ['html', 'clear-text', 'progress'],
  testRunner: 'vitest',
  vitest: {
    configFile: 'vitest.config.ts',
  },
  mutate: [
    'src/lib/**/*.ts',
    'src/domain/**/*.ts',
    '!src/**/*.test.ts',
    '!src/**/*.test.tsx',
    '!src/**/*.stories.tsx',
  ],
  thresholds: {
    high: 80,
    low: 50,
    break: 50,
  },
  htmlReporter: {
    fileName: 'reports/mutation/mutation-report.html',
  },
};

export default config;
