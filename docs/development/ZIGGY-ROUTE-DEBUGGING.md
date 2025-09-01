# Ziggy Route Debugging Guide

## Common Ziggy Route Errors

When you see errors like:
```
Ziggy error: route 'admin.blog.index' is not in the route list
```

This means the route name doesn't exist in Laravel's route list.

## Quick Debugging Steps

### 1. Check Available Routes
```bash
php artisan route:list | grep [search_term]
```

Examples:
```bash
php artisan route:list | grep blog
php artisan route:list | grep admin
php artisan route:list | grep posts
```

### 2. Common Route Name Patterns
- Admin blog posts: `admin.blog.posts.index`
- Admin blog categories: `admin.blog.categories.index`  
- Admin blog tags: `admin.blog.tags.index`
- Public blog: `blog.index`

### 3. Clear Caches After Route Changes
```bash
php artisan route:clear
php artisan config:clear
npm run build  # or restart npm run dev
```

## Prevention Strategies

### 1. Route Validation Script
Run before committing changes with route() calls:
```bash
php artisan route:list --json > route-check.json
```

### 2. Common Mistakes in Blog Module
- ❌ `admin.blog.index` (doesn't exist)
- ✅ `admin.blog.posts.index` (correct)
- ❌ `blog.admin.posts.index` (wrong order)
- ✅ `admin.blog.posts.index` (correct)

### 3. Check Route Files
- Admin blog routes: `app/Features/Blog/routes/admin.php`
- Public blog routes: `app/Features/Blog/routes/web.php`
- Main admin routes: `routes/admin.php`

### 4. Inertia.js Route Helper Usage
Always use the `route()` helper instead of hardcoded URLs:
```tsx
// ❌ Bad
href="/admin/blog/posts"

// ✅ Good  
href={route('admin.blog.posts.index')}
```

## Troubleshooting Checklist

1. **Route exists?** Check `php artisan route:list`
2. **Route name correct?** Verify exact spelling and structure
3. **Caches cleared?** Run cache clearing commands
4. **Server restarted?** Restart both Laravel and Vite dev servers
5. **Ziggy configuration?** Check `config/ziggy.php` if it exists

## Emergency Fixes

If you need to quickly fix a broken route:

1. **Find the correct route name:**
   ```bash
   php artisan route:list | grep [partial_name]
   ```

2. **Update the component:**
   ```tsx
   // Replace broken route
   href={route('correct.route.name')}
   ```

3. **Test immediately:**
   - Check browser console for errors
   - Verify navigation works
   - Test all affected pages

## Best Practices

1. **Always verify routes exist** before using them in components
2. **Use route name patterns** that match Laravel conventions
3. **Clear caches** after route changes
4. **Test navigation** thoroughly after route updates
5. **Document custom routes** in this file or route files