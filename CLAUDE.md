# Project Instructions for Claude

## Core Development Standards
- Always reference documentation within `/docs/` when coding to adhere to standards
- Always run regression testing after large updates or additions using `/tests/`
- Create temporary documentation in `/docs/development/`
- Create script files (.bat) in `/scripts/`

## Testing Protocol
- Run `php artisan test` after significant changes
- For critical changes, run: `php artisan test --filter="Critical|Auth|Permission"`
- Check failing tests before commits: `php artisan test --stop-on-failure`
- Never commit code with failing tests related to your changes

## Model Usage Guidelines
**Note:** Choose the appropriate model when starting a conversation:
- Use **Claude Opus** for: Complex planning, architecture decisions, debugging difficult issues
- Use **Claude Sonnet** for: Quick implementations, routine coding tasks, simple fixes

## Development Workflow
1. **Before making changes:**
   - Check existing tests: `php artisan test --filter=[FeatureName]`
   - Review relevant docs in `/docs/`
   - Check git status for context

2. **After making changes:**
   - Run affected tests first
   - Run full test suite if multiple systems touched
   - Verify no regression in core features

3. **Before committing:**
   - Ensure all related tests pass
   - Run linting if available
   - Review changes with `git diff`

## Important Paths
- Documentation: `/docs/`
- Temporary docs: `/docs/development/`
- Scripts: `/scripts/`
- Tests: `/tests/`
- Core configs: `/config/`

## Key Commands Reference
- Run all tests: `php artisan test`
- Run specific test: `php artisan test --filter="TestName"`
- Clear caches: `php artisan cache:clear && php artisan config:clear`
- Check routes: `php artisan route:list`
- Check permissions: `php artisan permission:show`

## Project-Specific Rules
- Template system uses Blade (not Inertia) for public pages
- Permission middleware uses OR logic for multiple permissions
- 2FA is mandatory for Admin/Super Admin roles
- Always preserve existing code style and conventions