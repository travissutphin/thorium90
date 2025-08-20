# Thorium90 Boilerplate - Change Management Workflow

## Overview

This guide covers how to manage changes to the Thorium90 boilerplate system while maintaining compatibility for existing and future client projects.

## Branch Strategy

### Main Branches
- **`main`** - Stable boilerplate (what clients install)
- **`develop`** - Integration branch for new features
- **`release/vX.X.X`** - Release preparation and testing

### Development Branches
- **`feature/setup-*`** - Setup wizard improvements
- **`feature/preset-*`** - New project presets  
- **`feature/config-*`** - Configuration enhancements
- **`hotfix/vX.X.X`** - Critical fixes for released versions

## When You Make Changes

### 1. For New Features

```bash
# Create feature branch from develop
git checkout develop
git pull origin develop
git checkout -b feature/new-preset-portfolio

# Make your changes
# Add files, modify code, update tests

# Test the changes
composer run test
php artisan thorium90:setup --preset=portfolio --name="Test Portfolio"

# Commit and push
git add .
git commit -m "feat: Add portfolio preset with gallery modules"
git push -u origin feature/new-preset-portfolio

# Create PR to develop branch
```

### 2. For Bug Fixes

```bash
# Create fix branch from main
git checkout main  
git pull origin main
git checkout -b fix/setup-wizard-validation

# Fix the issue
# Test the fix

# Commit and push
git add .
git commit -m "fix: Resolve setup wizard email validation issue"
git push -u origin fix/setup-wizard-validation

# Create PR to main
```

### 3. For Breaking Changes

```bash
# Create feature branch
git checkout develop
git checkout -b feature/major-auth-overhaul

# Make breaking changes
# Update setup wizard accordingly
# Update documentation

# Increment version appropriately (v2.0.0)
# Create comprehensive migration guide
```

## Version Management

### Semantic Versioning
- **MAJOR** (v2.0.0) - Breaking changes requiring client updates
- **MINOR** (v1.1.0) - New features, backward compatible
- **PATCH** (v1.0.1) - Bug fixes, backward compatible

### When to Increment

#### Patch Version (v1.0.1)
- Bug fixes in setup wizard
- Documentation updates
- Security patches
- Environment template fixes

```bash
git tag -a v1.0.1 -m "Fix: Setup wizard email validation"
```

#### Minor Version (v1.1.0)  
- New project presets
- Additional setup options
- New composer commands
- New environment variables (with defaults)

```bash
git tag -a v1.1.0 -m "Add portfolio preset and enhanced branding options"
```

#### Major Version (v2.0.0)
- Laravel framework upgrades
- Breaking changes to setup wizard
- Removal of deprecated features
- Database schema changes

```bash
git tag -a v2.0.0 -m "Laravel 13 upgrade with breaking changes"
```

## Testing Changes

### Before Committing

```bash
# 1. Test current functionality
composer run test
php artisan test --filter="Thorium90"

# 2. Test setup wizard
php artisan thorium90:setup --preset=default --name="Test Setup"

# 3. Test in clean environment
rm -rf /tmp/test-thorium90
composer create-project thorium90/boilerplate /tmp/test-thorium90
cd /tmp/test-thorium90
php artisan serve
```

### Integration Testing

```bash
# Test all presets work
php artisan thorium90:setup --preset=default
php artisan thorium90:setup --preset=ecommerce  
php artisan thorium90:setup --preset=blog
php artisan thorium90:setup --preset=saas
```

## Release Process

### 1. Prepare Release

```bash
# Create release branch from develop
git checkout develop
git pull origin develop
git checkout -b release/v1.1.0

# Update version numbers
# Update CHANGELOG.md
# Final testing
```

### 2. Create Release

```bash
# Merge to main
git checkout main
git merge release/v1.1.0

# Tag release
git tag -a v1.1.0 -m "Release v1.1.0: Add portfolio preset"

# Push everything
git push origin main
git push origin v1.1.0

# Merge back to develop
git checkout develop
git merge main
git push origin develop
```

### 3. Update Packagist

Packagist auto-updates via GitHub webhook, but verify:
1. Check [packagist.org/packages/thorium90/boilerplate](https://packagist.org/packages/thorium90/boilerplate)
2. Ensure new version appears
3. Test installation: `composer create-project thorium90/boilerplate test-v1.1.0`

## Managing Client Compatibility

### Backward Compatibility Rules

#### ✅ Safe Changes (Patch/Minor)
- Add new presets
- Add new configuration options (with defaults)
- Add new commands
- Enhance existing features without changing behavior
- Add new environment variables (optional)

#### ⚠️ Breaking Changes (Major)
- Remove presets
- Change setup wizard prompts
- Rename configuration keys
- Remove commands
- Change required environment variables
- Laravel framework upgrades

### Migration Guides

For breaking changes, create migration guides:

```bash
# docs/upgrades/v1-to-v2.md
# docs/upgrades/v2-to-v3.md
```

## Communication Strategy  

### 1. Changelog Maintenance

Update `CHANGELOG.md` with every release:

```markdown
# Changelog

## [1.1.0] - 2024-01-15
### Added
- Portfolio preset with gallery modules
- Enhanced branding customization
- New composer commands

### Fixed  
- Setup wizard email validation
- Environment template syntax
```

### 2. GitHub Releases

Create GitHub releases with:
- Release notes
- Breaking changes (if any)
- Migration instructions
- Download links

### 3. Documentation Updates

Always update:
- `README.boilerplate.md`
- `INSTALLATION.md`
- Setup command help text
- Configuration examples

## Hotfix Process

### For Critical Issues

```bash
# Create hotfix from main
git checkout main
git checkout -b hotfix/v1.0.1-security-patch

# Fix critical issue
# Test thoroughly

# Release immediately
git checkout main
git merge hotfix/v1.0.1-security-patch
git tag -a v1.0.1 -m "Security: Fix admin authentication bypass"
git push origin main v1.0.1

# Merge to develop
git checkout develop  
git merge main
git push origin develop
```

## Your Development Workflow

Since you're continuing to modify `/thorium90`:

### Daily Development
```bash
# Work on main branch for ongoing features
git checkout main
# Make changes
git add .
git commit -m "feat: Add new feature X"
git push origin main
```

### When Ready to Release
```bash  
# Create release branch
git checkout -b release/v1.1.0
# Update version, test, tag
git tag -a v1.1.0 -m "Release notes"
git push origin v1.1.0
```

### For Client Projects  
```bash
# Clients always install from latest stable tag
composer create-project thorium90/boilerplate client-project

# Or specific version
composer create-project thorium90/boilerplate:1.0.0 client-project
```

This workflow ensures your ongoing development doesn't break existing client installations while providing a clear upgrade path.