export default {
  "**/*.php": ["composer analyse", "composer format"],
  "*.js": [() => "pnpm run build"],
};
