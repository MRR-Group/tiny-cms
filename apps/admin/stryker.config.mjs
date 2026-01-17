/** @type {import('@stryker-mutator/api/core').PartialStrykerOptions} */
const config = {
  packageManager: 'pnpm',
  reporters: ['html', 'clear-text', 'progress'],
  testRunner: 'vitest',
  plugins: ['@stryker-mutator/vitest-runner'],
  vitest: {
    configFile: 'vitest.config.ts',
  },
  mutate: [
    'src/lib/**/*.ts',
    'src/domain/**/*.ts',
    '!src/**/*.test.ts',
    '!src/vite-env.d.ts',
    '!src/**/*.stories.tsx',
    '!src/**/index.ts',
  ],
  thresholds: {
    high: 100,
    low: 100,
    break: 100,
  },
  htmlReporter: {
    fileName: 'reports/mutation/mutation-report.html',
  },
};

export default config;
