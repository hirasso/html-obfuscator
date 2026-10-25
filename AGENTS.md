# For the AGENTS

This is a libary to obfuscate emails and phone numbers in HTML using modern web technology. PHP 8.4, TypeScript, Rolldown.

## Key commands

| Command | Description |
|---|---|
| `composer test` | Run the test suite |
| `composer test:coverage` | Run tests with clover coverage output |
| `composer analyse` | Run PHPStan static analysis |
| `composer format` | Format the code |
| `composer generate-types` | Generate TypeScript types after PHP code was changed |
| `pnpm build` | Build `resources/dist` from `resources/src` |
| `pnpm analyse` | TypeScript type-check |
| `pnpm test:unit` | Run vitest unit tests |
| `pnpm test:e2e` | Run e2e tests |

## Code style

- derive from the existing code
- prefix getters with `get`
- keep comments as short as possible
- do not add comments if not really required
- only use languages I know. No python please
- Use `/** */` block comments (not `//`) for anything that's not an "end of the line" comment
- docblocks state _what_ a thing does, never _why_ it does it or why it was built that way (exception: Inline docs like e.g. "using the raw value because..."); no {@see} cross-references at all; skip the docblock entirely if the name is self-explanatory; hard ceiling of 3 lines, shorter preferred.
- Exception: operational knowledge that would cause a bug if lost (a library gotcha, "deliberately absent" behavior) — relocate it to a new ADR under `docs/adr/` (only if definitely important, max terse like inline comments), then trim the code docblock to the rule above.

## Before making changes

- if the change is big, always first propose what you are planning, even if not in plan mode

## After making changes

- Keep inline code comments as short as possible. Be extremely concise and sacrifice grammar for the sake of concision
- Reference GitHub issues solved by the current edit (see #issue-tracker and #triage-labels).
- If it makes sense for a change, suggest a changeset message and level (patch/minor/major) and write it into the `./.changeset` folder. Commit it together with the changes

### PHP

- Prefer new features available in the PHP version declared in `composer.json`
- Classes in `CamelCase`, methods in `camelCase`
- After making changes, run `composer analyse` and `composer test`
- Add matching tests for new features. Don't overdo it!
- Keep code DRY! Ask if unsure.
- Prefer fewer, bigger classes: bundle related functionality in one class/file instead of many small ones. A class doing several related, contained jobs is fine

### TypeScript

- never edit files in `resources/dist` directly. Edit `resources/src` instead and run `pnpm build` to generate the dist files.
- after changes, run `pnpm analyse`, `pnpm test:unit` and `pnpm test:e2e`

## Do not touch without explicit request

- No commits without explicit request
- do not edit TODO files

## Agent skills

### Issue tracker

Issues and PRDs live as GitHub issues in `hirasso/html-obfuscator`. Use the `gh` CLI (`gh issue list/view/create/comment`).

### Triage labels

Canonical vocabulary as GitHub labels (`needs-triage`, `needs-info`, `ready-for-agent`, `ready-for-human`, `wontfix`).

Solved issues get closed via `Closes #<n>` in the commit message, never manually before the fix is merged.

### Domain docs

Single-context layout — one `CONTEXT.md` + `docs/adr/` at the repo root. See `docs/agents/domain.md`.

### Charts from the /improve-codebase-architecture skill

Store them inside `.scratch/charts` folder
