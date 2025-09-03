# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

- **Important** Always Start each reply with the Claude Model in use and display in the terminal
- You are a senior laravel engineer with attention to detail and best practices in mind (keep it simple if it accomplishes the request)
- **Always** ensure we are following best practices as laid out in this file
- **Always** suggest changes based on best practices even for existing code
- When you have completed each request, always review if cache needs cleared or if npm run build need executed to ensure a complete solution

## Architecture Overview

**Thorium90** is a Laravel 12 application built for production CMS applications with a hybrid frontend approach:

### Core Architecture
- **Backend**: Laravel 12 with feature-based modular structure
- **Frontend**: Hybrid approach - Inertia.js React for admin, Blade templates for public pages
- **Database**: MySQL production / SQLite development with comprehensive migrations
- **Authentication**: Laravel Fortify + Spatie Permissions (2FA infrastructure present but not active)
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

### Development Setup (Bulletproof v2.1.0 Workflow)
```bash
# STEP 1: System validation first (prevents 95% of setup issues)
npm run health-check                    # Comprehensive system validation
composer run health-check              # Alternative command

# STEP 2: Interactive setup (recommended for all users)
php artisan thorium90:setup --interactive  # Guided setup wizard
# OR for advanced users who understand the risks:
php artisan thorium90:setup --force        # Skip validation (not recommended)

# STEP 3: Development workflow
npm run build        # Production build (required after setup)
php artisan serve    # Laravel server (http://localhost:8000)
npm run dev          # Vite dev server (for live reloading)

# Advanced development commands
composer run fresh-start               # Complete setup from scratch
composer run dev                       # All services (server, queue, logs, vite)
npm run dev:check                      # TypeScript + ESLint validation
npm run dev:https                      # HTTPS development server
npm run build:ssr                      # SSR build
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

### Bulletproof Setup Protocol (v2.1.0)
- **ALWAYS** run `npm run health-check` before any development work
- **NEVER** proceed with setup if system validation fails
- **MANDATORY** use `php artisan thorium90:setup --interactive` for new installations
- Only advanced users should use `--force` flag to bypass validation
- **REQUIRED** run `npm run build` after successful setup

### Mandatory Testing Protocol  
- **ALWAYS** run `scripts\test-regression.bat` before committing ANY changes
- **NEVER SKIP** regression testing when modifying existing functionality
- Run `php artisan test --filter="Critical|Auth|Permission"` for critical changes
- Never commit code with failing tests related to your changes
- Use `npm run dev:check` for TypeScript and linting validation before commits

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

## Terminology & Naming Standards (CRITICAL)

### Project References
- **ALWAYS use**: "Thorium90" (exact capitalization)
- **NEVER use**: "Thorium90 boilerplate", "thorium90", "Thorium-90", "Thorium 90"
- **File references**: Use exact case-sensitive paths and filenames
- **Documentation**: Refer to project as "Thorium90" in all contexts

### Feature Status Terminology
- **Active Features**: Currently implemented and ready for use
- **Integrated Features**: Partially implemented, may have incomplete functionality
- **Planned Features**: Not yet implemented, avoid referencing as available

## Feature Implementation Status (MANDATORY REFERENCE)

### ✅ ACTIVE FEATURES (Safe to reference and build upon)
- **Multi-role Authentication**: Laravel Fortify + Spatie Permissions (fully implemented)
- **Blog System**: Complete blog functionality in `app/Features/Blog/`
- **AEO Integration**: AI-powered content analysis (Claude, OpenAI, Basic providers)
- **Page Management**: Dynamic page system with templates
- **Admin Dashboard**: Inertia.js React admin interface
- **Bulletproof Deployment**: v2.1.0 health check and setup system

### ⚠️ PARTIALLY INTEGRATED FEATURES (Use with caution)
- **2FA (Two-Factor Authentication)**: 
  - Status: Infrastructure in place but ON HOLD
  - **DO NOT**: Reference as "mandatory for admin roles" 
  - **DO NOT**: Include 2FA setup instructions
  - **CURRENT STATE**: Basic Fortify 2FA structure exists but not activated
  - **WHEN TO MENTION**: Only if user explicitly asks about 2FA implementation

### ❌ PLANNED FEATURES (Do not reference as available)
- E-commerce functionality
- Advanced team management
- Subscription billing
- Multi-tenancy

## Architectural Precision Standards

### Database References
- **Primary Development**: SQLite (zero configuration)
- **Production Deployment**: MySQL (recommended)
- **NEVER assume**: PostgreSQL is actively supported (it's configured but not primary)

### Frontend Architecture
- **Admin Interface**: Inertia.js + React (resources/js/pages/)
- **Public Pages**: Blade templates (resources/views/)
- **NEVER mix**: Don't suggest Inertia for public pages or Blade for admin

### Permission System Reality Check
- **Current Implementation**: Role-based with Spatie Permissions
- **Middleware Logic**: OR logic for multiple permissions (not AND)
- **2FA Integration**: NOT CURRENTLY ACTIVE (despite infrastructure presence)

## Code Generation Standards

### File Creation Rules
- **ALWAYS check existing patterns** before generating new files
- **Use exact existing directory structure** - don't create new base folders
- **Follow established naming conventions** in sibling files
- **Preserve existing code style** and formatting patterns

### Component Development
- **Reuse existing components** before creating new ones
- **Check resources/js/components/** for existing UI patterns
- **Use shadcn/ui components** that are already installed
- **Follow TypeScript definitions** in resources/js/types/

### Service Integration
- **Blog services**: Use existing BlogService patterns
- **AI services**: Use AIContentAnalyzerInterface abstraction
- **Media uploads**: Follow established MediaUploadService patterns

## Common Mistakes & Corrections

### ❌ AVOID THESE MISTAKES:
- Referring to "Thorium90 boilerplate" instead of "Thorium90"
- Mentioning 2FA as mandatory or fully implemented
- Using `--silent` flag (removed in v2.1.0)
- Suggesting PostgreSQL as primary database
- Creating new base directories without approval
- Mixing Inertia/Blade contexts incorrectly
- Assuming e-commerce features are available

### ✅ CORRECT APPROACHES:
- Always say "Thorium90" (exact case)
- Mention 2FA only if user specifically asks
- Use `--interactive` for setup commands
- Default to SQLite for development examples
- Follow existing directory patterns
- Use appropriate template system for context
- Focus on implemented features (blog, pages, auth, AEO)

## User Interaction Guidelines

### Question Clarification
- **When user mentions "boilerplate"**: Gently correct to "Thorium90"
- **When user asks about 2FA**: Clarify current status (infrastructure exists but on hold)
- **When user requests e-commerce**: Explain it's not currently implemented
- **Always confirm**: Feature availability before providing implementation details

### Technical Recommendations
- **Default to implemented features** when suggesting solutions
- **Validate feature status** before providing detailed implementation
- **Suggest alternatives** if requested feature isn't fully available
- **Be transparent** about partial implementations vs full features

## Project-Specific Rules
- Template system uses Blade (not Inertia) for public pages
- Permission middleware uses OR logic for multiple permissions
- 2FA infrastructure is present but currently on hold for future implementation
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
This project serves as a base template for client projects. Follow these rules:

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
- **Patch (v2.1.1)**: Bug fixes, security patches, minor command updates
- **Minor (v2.2.0)**: New features, new commands, enhanced functionality (backward compatible)
- **Major (v3.0.0)**: Breaking changes, framework upgrades, PHP version requirements

**Always Test Before Release:**
- Run `npm run health-check` to ensure system validation works
- Run `php artisan test` and `scripts\test-regression.bat` 
- Test setup wizard: `php artisan thorium90:setup --interactive`
- Test clean install: `composer create-project thorium90/thorium90 test-install`
- Test failure scenarios: Try setup with missing PHP extensions or wrong versions
- **v2.1.0+ Requirement**: Test health check → setup → build → serve workflow end-to-end

**Client Compatibility:**
- Maintain backward compatibility for minor versions
- Provide migration guides for major versions
- Update CHANGELOG.md with every release

See `/docs/development/BOILERPLATE-WORKFLOW.md` for complete workflow.

## Important Paths
- Documentation: `/docs/`
- Temporary docs: `/docs/development/`
- Scripts: `/scripts/` (includes health-check.js, test-regression.bat)
- Tests: `/tests/`
- Core configs: `/config/`
- Blog feature: `/app/Features/Blog/`
- Frontend components: `/resources/js/components/`

## v2.1.0 Bulletproof System Files
- **Setup State**: `.thorium90-setup` (tracks setup completion and metadata)
- **Health Check**: `scripts/health-check.js` (comprehensive system validation)
- **Enhanced Setup**: `app/Console/Commands/Thorium90Setup.php` (bulletproof setup command)
- **Environment Template**: `.env.example` (updated with security defaults)
- **Enhanced Vite**: `vite.config.ts` (cross-platform, HTTPS, dynamic ports)
- **Updated Dependencies**: `composer.json` & `package.json` (new commands, PHP 8.2+)

## Deployment Status Tracking
- **Setup Completion**: Check for `.thorium90-setup` file to determine if project is configured
- **System Validation**: Use `npm run health-check` to verify environment before development
- **Recovery Mode**: Setup command provides guided recovery when validation fails
- **State Persistence**: Setup metadata includes versions, database type, completion timestamp

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context
This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.2+ (minimum requirement for Laravel 12 + v2.1.0 bulletproof deployment)
- inertiajs/inertia-laravel (INERTIA) - v2
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v4
- laravel/socialite (SOCIALITE) - v5
- tightenco/ziggy (ZIGGY) - v2
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- @inertiajs/react (INERTIA) - v2
- react (REACT) - v19
- tailwindcss (TAILWINDCSS) - v4
- eslint (ESLINT) - v9
- prettier (PRETTIER) - v3


## Conventions
- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts
- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture
- Stick to existing directory structure - don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling
- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies
- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files
- You must only create documentation files if explicitly requested by the user.


=== boost rules ===

## Laravel Boost
- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan
- Use the `list-artisan-commands` tool when you need to call an Artisan command to double check the available parameters.

## URLs
- Whenever you share a project URL with the user you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain / IP, and port.

## Tinker / Debugging
- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool
- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)
- Boost comes with a powerful `search-docs` tool you should use before any other approaches. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation specific for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The 'search-docs' tool is perfect for all Laravel related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel-ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries - package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax
- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit"
3. Quoted Phrases (Exact Position) - query="infinite scroll" - Words must be adjacent and in that order
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit"
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms


=== php rules ===

## PHP

- Always use curly braces for control structures, even if it has one line.

### Constructors
- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters.

### Type Declarations
- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments
- Prefer PHPDoc blocks over comments. Never use comments within the code itself unless there is something _very_ complex going on.

## PHPDoc Blocks
- Add useful array shape type definitions for arrays when appropriate.

## Enums
- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.


=== inertia-laravel/core rules ===

## Inertia Core

- Inertia.js components should be placed in the `resources/js/Pages` directory unless specified differently in the JS bundler (vite.config.js).
- Use `Inertia::render()` for server-side routing instead of traditional Blade views.
- Use `search-docs` for accurate guidance on all things Inertia.

<code-snippet lang="php" name="Inertia::render Example">
// routes/web.php example
Route::get('/users', function () {
    return Inertia::render('Users/Index', [
        'users' => User::all()
    ]);
});
</code-snippet>


=== inertia-laravel/v2 rules ===

## Inertia v2

- Make use of all Inertia features from v1 & v2. Check the documentation before making any changes to ensure we are taking the correct approach.

### Inertia v2 New Features
- Polling
- Prefetching
- Deferred props
- Infinite scrolling using merging props and `WhenVisible`
- Lazy loading data on scroll

### Deferred Props & Empty States
- When using deferred props on the frontend, you should add a nice empty state with pulsing / animated skeleton.

### Inertia Form General Guidance
- Build forms using the `useForm` helper. Use the code examples and `search-docs` tool with a query of `useForm helper` for guidance.


=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database
- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation
- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources
- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation
- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues
- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization
- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation
- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration
- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing
- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] <name>` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error
- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.


=== laravel/v12 rules ===

## Laravel 12

- Use the `search-docs` tool to get version specific documentation.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

### Laravel 12 Structure
- No middleware files in `app/Http/Middleware/`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- **No app\Console\Kernel.php** - use `bootstrap/app.php` or `routes/console.php` for console configuration.
- **Commands auto-register** - files in `app/Console/Commands/` are automatically available and do not require manual registration.

### Database
- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 11 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models
- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.


=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.


=== inertia-react/core rules ===

## Inertia + React

- Use `router.visit()` or `<Link>` for navigation instead of traditional links.

<code-snippet name="Inertia Client Navigation" lang="react">

import { Link } from '@inertiajs/react'
<Link href="/">Home</Link>

</code-snippet>


=== inertia-react/v2 rules ===

## Inertia + React Forms

<code-snippet name="Inertia React useForm Example" lang="react">

import { useForm } from '@inertiajs/react'

const { data, setData, post, processing, errors } = useForm({
    email: '',
    password: '',
    remember: false,
})

function submit(e) {
    e.preventDefault()
    post('/login')
}

return (
<form onSubmit={submit}>
    <input type="text" value={data.email} onChange={e => setData('email', e.target.value)} />
    {errors.email && <div>{errors.email}</div>}
    <input type="password" value={data.password} onChange={e => setData('password', e.target.value)} />
    {errors.password && <div>{errors.password}</div>}
    <input type="checkbox" checked={data.remember} onChange={e => setData('remember', e.target.checked)} /> Remember Me
    <button type="submit" disabled={processing}>Login</button>
</form>
)

</code-snippet>


=== tailwindcss/core rules ===

## Tailwind Core

- Use Tailwind CSS classes to style HTML, check and use existing tailwind conventions within the project before writing your own.
- Offer to extract repeated patterns into components that match the project's conventions (i.e. Blade, JSX, Vue, etc..)
- Think through class placement, order, priority, and defaults - remove redundant classes, add classes to parent or child carefully to limit repetition, group elements logically
- You can use the `search-docs` tool to get exact examples from the official documentation when needed.

### Spacing
- When listing items, use gap utilities for spacing, don't use margins.

    <code-snippet name="Valid Flex Gap Spacing Example" lang="html">
        <div class="flex gap-8">
            <div>Superior</div>
            <div>Michigan</div>
            <div>Erie</div>
        </div>
    </code-snippet>


### Dark Mode
- If existing pages and components support dark mode, new pages and components must support dark mode in a similar way, typically using `dark:`.


=== tailwindcss/v4 rules ===

## Tailwind 4

- Always use Tailwind CSS v4 - do not use the deprecated utilities.
- `corePlugins` is not supported in Tailwind v4.
- In Tailwind v4, you import Tailwind using a regular CSS `@import` statement, not using the `@tailwind` directives used in v3:

<code-snippet name="Tailwind v4 Import Tailwind Diff" lang="diff"
   - @tailwind base;
   - @tailwind components;
   - @tailwind utilities;
   + @import "tailwindcss";
</code-snippet>


### Replaced Utilities
- Tailwind v4 removed deprecated utilities. Do not use the deprecated option - use the replacement.
- Opacity values are still numeric.

| Deprecated |	Replacement |
|------------+--------------|
| bg-opacity-* | bg-black/* |
| text-opacity-* | text-black/* |
| border-opacity-* | border-black/* |
| divide-opacity-* | divide-black/* |
| ring-opacity-* | ring-black/* |
| placeholder-opacity-* | placeholder-black/* |
| flex-shrink-* | shrink-* |
| flex-grow-* | grow-* |
| overflow-ellipsis | text-ellipsis |
| decoration-slice | box-decoration-slice |
| decoration-clone | box-decoration-clone |


=== tests rules ===

## Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test` with a specific filename or filter.
</laravel-boost-guidelines>