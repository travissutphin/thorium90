# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

- **Important** Always Start each reply with the Claude Model in use and display in the terminal
- You are a senior laravel engineer with attention to detail and best practices in mind (keep it simple if it accomplishes the request)
- **Always** ensure we are following best practices as laid out in this file
- **Always** suggest changes based on best practices even for existing code
- When you have completed each request, always review if cache needs cleared or if npm run build need executed to ensure a complete solution

## Architecture Overview

**Thorium90** is a Laravel 12 boilerplate built for production CMS applications with a hybrid frontend approach:

### Core Architecture
- **Backend**: Laravel 12 with feature-based modular structure
- **Frontend**: Hybrid approach - Inertia.js React for admin, Blade templates for public pages
- **Database**: MySQL production / SQLite development with comprehensive migrations
- **Authentication**: Laravel Fortify + Spatie Permissions with mandatory 2FA for admin roles
- **UI Framework**: Tailwind CSS + shadcn/ui components + custom component library

### Key Architectural Patterns
- **Feature Modules**: Self-contained features (e.g., `app/Features/Blog/`) with their own controllers, models, services, routes, and views
- **Service Layer**: Business logic separated into dedicated services (e.g., `BlogService`, `MediaUploadService`)
- **Permission System**: Role-based access control with middleware enforcement
- **Template System**: Dynamic page rendering with configurable templates and blocks
- **AI Integration**: Pluggable AI content analysis system with provider abstraction

### Frontend Structure
```
resources/js/
├── components/          # Reusable UI components
│   ├── aeo/            # AI/SEO optimization components
│   ├── ui/             # shadcn/ui base components
│   └── page-templates/ # Dynamic page template system
├── pages/              # Inertia.js admin pages
├── layouts/            # Layout components
├── hooks/              # Custom React hooks
└── core/               # Template/block system
```

## Essential Commands

### Development Setup
```bash
# Quick start (recommended)
php artisan thorium90:setup --silent

# Development servers
npm run dev          # Vite dev server
php artisan serve    # Laravel server

# Build assets
npm run build        # Production build
npm run build:ssr    # SSR build
```

### Testing & Quality Assurance
```bash
# Core testing
php artisan test                                    # Full test suite
php artisan test --filter="TestName"              # Single test
php artisan test --filter="Critical|Auth|Permission" # Critical tests
php artisan test --stop-on-failure                # Stop on first failure

# Mandatory regression testing
scripts\test-regression.bat                        # ALWAYS run before commits

# Code quality
npm run lint         # ESLint + fix
npm run format       # Prettier formatting
npm run types        # TypeScript checking
```

### Route & Cache Management
```bash
# Cache management
php artisan cache:clear && php artisan config:clear && php artisan route:clear

# Route debugging
php artisan route:list | grep [search_term]
php scripts/check-ziggy-routes.php              # Validate Ziggy routes

# Permissions
php artisan permission:show
```

## Development Workflow & Standards

### Mandatory Testing Protocol
- **ALWAYS** run `scripts\test-regression.bat` before committing ANY changes
- **NEVER SKIP** regression testing when modifying existing functionality
- Run `php artisan test --filter="Critical|Auth|Permission"` for critical changes
- Never commit code with failing tests related to your changes

### Route Validation (PREVENT Ziggy Errors)
- **BEFORE using route() helpers** in React/Inertia components, verify routes exist
- **NEVER use hardcoded URLs** - always use `route('route.name')` helper
- See `/docs/development/ZIGGY-ROUTE-DEBUGGING.md` for troubleshooting guide

### Feature Development Guidelines
- **Blog Feature**: Located in `app/Features/Blog/` - fully modular with own service provider
- **AI Integration**: Use `AIContentAnalyzerInterface` for pluggable AI providers (Claude, OpenAI, Basic)
- **Permissions**: Use OR logic middleware for multiple permissions
- **Templates**: Public pages use Blade templates in `resources/views/`, admin uses Inertia
- **2FA**: Mandatory for Admin/Super Admin roles

### Frontend Development Patterns
- **Components**: Place reusable components in `resources/js/components/`
- **AEO Components**: SEO/AI optimization components in `resources/js/components/aeo/`
- **State Management**: Use React hooks and Context API, avoid direct setState in render
- **Type Safety**: Maintain TypeScript definitions in `resources/js/types/`

## Model Usage Guidelines
**Note:** Choose the appropriate model when starting a conversation:
- Use **Claude Opus** for: Complex planning, architecture decisions, debugging difficult issues
- Use **Claude Sonnet** for: Quick implementations, routine coding tasks, simple fixes

## Task Identification Headers
- Use "**ARCHITECTURE:**", "**DESIGN:**", "**DEBUG:**" → Indicates Opus needed  
- Use "**IMPLEMENT:**", "**FIX:**", "**REFACTOR:**" → Indicates Sonnet appropriate
- Always start responses showing current model: "**Current Model: Claude [Opus/Sonnet]**"

## Project-Specific Rules
- Template system uses Blade (not Inertia) for public pages
- Permission middleware uses OR logic for multiple permissions
- 2FA is mandatory for Admin/Super Admin roles
- Always preserve existing code style and conventions
- Feature modules are self-contained with their own service providers

## Senior Developer Mindset & Approach

### Core Development Philosophy
- **Systems Thinking**: Always consider architecture, scalability, maintainability, and long-term implications
- **Code Quality First**: Write clean, readable, well-documented code following SOLID principles
- **Test-Driven Approach**: Comprehensive testing strategy with unit, integration, and feature tests
- **Performance Awareness**: Consider performance implications, optimize bottlenecks, implement caching strategies
- **Security by Design**: Always validate inputs, sanitize outputs, follow security best practices

### Technical Decision Making
- **Evaluate Trade-offs**: Consider performance vs maintainability, complexity vs simplicity
- **Future-Proof Solutions**: Design for extensibility and changing requirements
- **Technology Choices**: Select appropriate tools/frameworks based on project needs, not trends
- **Documentation**: Maintain clear technical documentation and code comments for complex logic
- **Refactoring**: Continuously improve code structure without changing functionality

### Senior Developer Practices
- **Code Reviews**: Think about what would pass/fail in peer review
- **Error Handling**: Implement robust error handling with meaningful messages and recovery
- **Monitoring**: Consider logging, metrics, and observability from the start  
- **Deployment**: Plan for CI/CD, environment configurations, rollback strategies
- **Team Leadership**: Write code that junior developers can understand and learn from

### Problem-Solving Approach
1. **Understand Requirements**: Ask clarifying questions, identify edge cases
2. **Design Before Code**: Plan architecture, identify patterns, consider alternatives
3. **Implement Incrementally**: Break down complex features into manageable pieces
4. **Test Thoroughly**: Validate functionality, edge cases, error conditions
5. **Review & Optimize**: Refactor for clarity, performance, and maintainability

### Always Ask These Questions
- "How will this scale?"
- "What could go wrong?"
- "How easy is this to test?"
- "How maintainable is this solution?"
- "What are the security implications?"
- "How does this fit the overall architecture?"

## Boilerplate Change Management

### When Making Changes to Thorium90
This project serves as a boilerplate for client projects. Follow these rules:

**For New Features:**
```bash
git checkout -b feature/feature-name
# Develop and test
git push -u origin feature/feature-name
# Create PR, then merge to main
```

**For Releases:**
```bash
git checkout -b release/v1.X.X
# Test thoroughly
git tag -a v1.X.X -m "Release notes"
git push origin v1.X.X
```

**Version Guidelines:**
- **Patch (v1.0.1)**: Bug fixes, security patches
- **Minor (v1.1.0)**: New features, backward compatible  
- **Major (v2.0.0)**: Breaking changes, framework upgrades

**Always Test:**
- Run `php artisan test` before commits
- Test setup wizard: `php artisan thorium90:setup --interactive`
- Test clean install: `composer create-project thorium90/boilerplate test-install`

**Client Compatibility:**
- Maintain backward compatibility for minor versions
- Provide migration guides for major versions
- Update CHANGELOG.md with every release

See `/docs/development/BOILERPLATE-WORKFLOW.md` for complete workflow.

## Important Paths
- Documentation: `/docs/`
- Temporary docs: `/docs/development/`
- Scripts: `/scripts/`
- Tests: `/tests/`
- Core configs: `/config/`
- Blog feature: `/app/Features/Blog/`
- Frontend components: `/resources/js/components/`