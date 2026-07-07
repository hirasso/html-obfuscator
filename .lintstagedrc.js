// @ts-check

/**
 * @type {import('lint-staged').Configuration}
 */
export default {
  "**/*.php": ["composer analyse", "composer format"],
  "*.ts": [() => "pnpm run build"],
  "*.(ts|php|md)": () => [
    "composer build",
    "pnpm run analyse",
    "git add resources/dist resources/src/generated",
    "git add demo/readme.generated.html",
  ],
};
