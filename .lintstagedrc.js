// @ts-check

/**
 * @type {import('lint-staged').Configuration}
 */
export default {
  "**/*.php": ["composer analyse", "composer format"],
  "*.ts": [() => "pnpm run build"],
  "*.(ts|php)": () => [
    "composer generate-types",
    "pnpm run analyse",
    "git add resources/dist",
  ],
};
