# thorium90 - Setup Guide

## Initial Setup Complete ✅

Your Thorium90 project has been configured automatically. Here's what was set up:

### Environment
- Project name configured
- Database connection established  
- Admin user created

### Next Steps

1. **Start Development Server**
   ```bash
   php artisan serve
   ```

2. **Access Admin Panel**
   - URL: http://localhost:8000/admin
   - Use the admin credentials you provided during setup

3. **Customize Your Site**
   - Edit pages in the admin panel
   - Configure site settings
   - Upload your logo and branding

4. **Development Commands**
   ```bash
   # Run tests
   php artisan test
   
   # Clear caches
   php artisan cache:clear
   
   # Run with queue processing
   composer run dev
   ```

## Configuration Files

- **Environment**: `.env`
- **Features**: `config/thorium90.php`
- **Database**: `config/database.php`

## Available Artisan Commands

```bash
php artisan thorium90:setup       # Re-run setup
php artisan thorium90:docs        # Generate documentation
php artisan thorium90:rebrand     # Update branding
```

---
*Need help? Check the [Thorium90 Documentation](https://thorium90.com/docs)*
