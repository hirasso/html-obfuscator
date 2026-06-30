# For Agents

This is a libary to obfuscate emails and phone numbers in HTML using modern web technology

## Key commands

| Command | Description |
|---|---|
| `composer test` | Run the test suite |
| `composer test:coverage` | Run tests with clover coverage output |
| `composer analyse` | Run PHPStan static analysis |
| `composer format` | Format the code |
| `composer generate-types` | Generate TypeScript types after PHP code was changed |
| `pnpm run test:e2e` | Run e2e tests |


## Writing new code

- add matching tests for each new feature
- run `composer test`, `composer analyse` and `pnpm run test:e2e`

## Commits

- Before committing a feature/fix, suggest a changeset message and level (patch/minor/major) and write it into the `./.changesets` folder. Commit it together with the changes

## Agent skills

### Domain docs

Single-context layout — one `CONTEXT.md` + `docs/adr/` at the repo root. See `docs/agents/domain.md`.