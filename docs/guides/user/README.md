# User Guide

## Overview

Welcome to Thorium90 CMS! This guide will help you understand how to use the system effectively, whether you're an administrator, content creator, or regular user.

## Getting Started

### Logging In

1. Navigate to your Thorium90 CMS login page
2. Enter your email address and password
3. Click "Sign In"

**First-time users**: If you don't have an account, contact your administrator or use the registration link if enabled.

### Dashboard Overview

After logging in, you'll see the dashboard with:
- **Navigation Menu**: Access to different sections based on your role
- **Quick Stats**: Overview of system activity
- **Recent Activity**: Latest actions and updates
- **Quick Actions**: Common tasks you can perform

## User Roles and Permissions

### Role Hierarchy

1. **Super Admin** - Full system access
2. **Admin** - Administrative access (no system settings)
3. **Editor** - Content management and user oversight
4. **Author** - Content creation and own content management
5. **Subscriber** - Basic access and profile management

### What Each Role Can Do

#### Super Admin
- ✅ All system functions
- ✅ Manage users and roles
- ✅ System settings and configuration
- ✅ Full content management
- ✅ View system statistics

#### Admin
- ✅ Manage users (except Super Admins)
- ✅ Assign roles (except Super Admin)
- ✅ Full content management
- ✅ View most system statistics
- ❌ System settings and configuration

#### Editor
- ✅ Manage all content (pages, media)
- ✅ Moderate user content
- ✅ View content statistics
- ❌ User management
- ❌ System settings

#### Author
- ✅ Create and manage own content
- ✅ Upload media files
- ✅ View own statistics
- ❌ Edit others' content
- ❌ User management

#### Subscriber
- ✅ View dashboard
- ✅ Update own profile
- ✅ Change password
- ❌ Content management
- ❌ Access to admin features

## Navigation

### Main Menu

The main navigation menu appears on the left side and includes:

- **Dashboard** - Overview and quick stats
- **Content** - Pages and media management
- **Users** - User management (Admin+ only)
- **Settings** - System configuration (Super Admin only)
- **Profile** - Your account settings

### Breadcrumbs

Use the breadcrumb navigation at the top to understand your current location and navigate back to parent sections.

## Content Management

### Managing Pages

#### Creating a New Page

1. Navigate to **Content → Pages**
2. Click **"Create New Page"**
3. Fill in the required information:
   - **Title**: The page title
   - **Slug**: URL-friendly version (auto-generated)
   - **Content**: Main page content
   - **Excerpt**: Brief description
   - **Status**: Draft, Published, or Private

4. **SEO Settings** (Optional):
   - Meta Title
   - Meta Description
   - Keywords
   - Schema Type

5. Click **"Save Draft"** or **"Publish"**

#### Editing Pages

1. Go to **Content → Pages**
2. Find the page you want to edit
3. Click the **"Edit"** button
4. Make your changes
5. Click **"Update"** to save

#### Page Status

- **Draft**: Not visible to public, work in progress
- **Published**: Live and visible to everyone
- **Private**: Only visible to logged-in users with permission

#### Publishing Workflow

1. **Create Draft**: Start with a draft to work on content
2. **Review**: Check content, SEO settings, and formatting
3. **Publish**: Make the page live for public viewing
4. **Update**: Make changes to published pages as needed

### Media Management

#### Uploading Files

1. Navigate to **Content → Media** (or use the media picker in page editor)
2. Click **"Upload Files"**
3. Drag and drop files or click to browse
4. Wait for upload to complete
5. Files are now available for use in pages

#### Supported File Types

- **Images**: JPG, PNG, GIF, WebP, SVG
- **Documents**: PDF, DOC, DOCX, TXT
- **Media**: MP4, MP3, WAV (if enabled)

#### Using Media in Pages

1. While editing a page, click the **"Add Media"** button
2. Select from existing files or upload new ones
3. Choose the file and click **"Insert"**
4. Adjust size and alignment as needed

## User Management (Admin+ Only)

### Creating Users

1. Navigate to **Users → All Users**
2. Click **"Add New User"**
3. Fill in user information:
   - Name
   - Email address
   - Password (or send invitation)
   - Role assignment

4. Click **"Create User"**

### Managing User Roles

1. Go to **Users → All Users**
2. Find the user you want to modify
3. Click **"Edit"** next to their name
4. Change their role in the dropdown
5. Click **"Update User"**

### User Status

- **Active**: User can log in and use the system
- **Inactive**: User account is disabled
- **Pending**: User hasn't completed registration

## Profile Management

### Updating Your Profile

1. Click your name in the top-right corner
2. Select **"Profile"**
3. Update your information:
   - Name
   - Email address
   - Profile picture
   - Bio/Description

4. Click **"Save Changes"**

### Changing Your Password

1. Go to your **Profile** page
2. Click **"Change Password"**
3. Enter your current password
4. Enter your new password twice
5. Click **"Update Password"**

### Two-Factor Authentication

1. In your **Profile**, find **"Two-Factor Authentication"**
2. Click **"Enable 2FA"**
3. Scan the QR code with your authenticator app
4. Enter the verification code
5. Save your recovery codes in a safe place

## Settings (Super Admin Only)

### General Settings

- **Site Name**: Your website's name
- **Site Description**: Brief description of your site
- **Timezone**: Your local timezone
- **Date Format**: How dates are displayed
- **Time Format**: 12-hour or 24-hour format

### Email Settings

- **Mail Driver**: How emails are sent
- **From Address**: Default sender email
- **From Name**: Default sender name

### Security Settings

- **Registration**: Allow new user registration
- **Email Verification**: Require email verification
- **Password Requirements**: Minimum password strength
- **Session Timeout**: How long users stay logged in

## Search and Filtering

### Global Search

Use the search box in the top navigation to find:
- Pages by title or content
- Users by name or email
- Settings by name

### Filtering Lists

Most list pages (Users, Pages) include filters:
- **Status Filter**: Show only active, inactive, etc.
- **Role Filter**: Filter users by role
- **Date Range**: Show items from specific time periods
- **Author Filter**: Show content by specific authors

## Keyboard Shortcuts

### Global Shortcuts

- `Ctrl/Cmd + K`: Open global search
- `Ctrl/Cmd + S`: Save current form
- `Esc`: Close modals and dropdowns

### Content Editor

- `Ctrl/Cmd + B`: Bold text
- `Ctrl/Cmd + I`: Italic text
- `Ctrl/Cmd + U`: Underline text
- `Ctrl/Cmd + Z`: Undo
- `Ctrl/Cmd + Y`: Redo

## Mobile Usage

### Responsive Design

Thorium90 CMS works on mobile devices with:
- Touch-friendly interface
- Responsive navigation menu
- Mobile-optimized forms
- Swipe gestures for lists

### Mobile Limitations

Some features work better on desktop:
- Complex content editing
- Bulk operations
- Detailed statistics views
- File management

## Troubleshooting

### Common Issues

#### Can't Log In
1. Check your email and password
2. Try the "Forgot Password" link
3. Contact your administrator
4. Clear browser cache and cookies

#### Page Won't Save
1. Check your internet connection
2. Ensure all required fields are filled
3. Try refreshing the page
4. Contact support if the issue persists

#### Images Won't Upload
1. Check file size (usually max 10MB)
2. Ensure file type is supported
3. Try a different browser
4. Check your internet connection

#### Permission Denied
1. You may not have the required role
2. Contact your administrator
3. Log out and log back in
4. Check if your account is active

### Getting Help

#### Built-in Help

- Look for **"?"** icons next to features
- Check tooltips by hovering over buttons
- Use the **"Help"** section in the main menu

#### Contact Support

1. **Documentation**: Check the full documentation
2. **Administrator**: Contact your system administrator
3. **Support**: Use the support contact information provided
4. **Community**: Join user forums or discussions

## Best Practices

### Content Creation

1. **Plan Your Content**: Outline before writing
2. **Use SEO**: Fill in meta descriptions and titles
3. **Optimize Images**: Compress images before uploading
4. **Preview**: Always preview before publishing
5. **Regular Backups**: Keep copies of important content

### Security

1. **Strong Passwords**: Use unique, complex passwords
2. **Enable 2FA**: Add extra security to your account
3. **Log Out**: Always log out on shared computers
4. **Regular Updates**: Keep your profile information current
5. **Report Issues**: Report suspicious activity immediately

### Collaboration

1. **Clear Communication**: Use descriptive page titles and comments
2. **Draft First**: Use drafts for collaborative editing
3. **Role Clarity**: Understand your role and permissions
4. **Regular Reviews**: Review and update content regularly
5. **Backup Important Work**: Save important content externally

## Advanced Features

### Bulk Operations

Select multiple items to:
- Delete multiple pages at once
- Change status of multiple items
- Export selected data
- Apply bulk edits

### Import/Export

- **Export**: Download your content as CSV or JSON
- **Import**: Upload content from other systems
- **Backup**: Create full system backups (Admin only)

### API Access

Developers can access the API for:
- Custom integrations
- Mobile app development
- Third-party tool connections
- Automated workflows

## Updates and Maintenance

### System Updates

Your administrator handles:
- Software updates
- Security patches
- Feature additions
- Performance improvements

### Your Responsibilities

- Keep your profile updated
- Use strong passwords
- Report bugs or issues
- Follow content guidelines
- Respect other users

## Conclusion

Thorium90 CMS is designed to be intuitive and powerful. This guide covers the basics, but don't hesitate to explore and experiment with features available to your role.

Remember:
- Start with simple tasks and build up to complex ones
- Use the help resources available
- Ask questions when you're unsure
- Keep your account secure
- Enjoy creating great content!

For more detailed technical information, see:
- [Developer Guide](Developer-Guide) - Technical implementation
- [API Reference](API-Reference) - API documentation
- [Installation Guide](Installation-Guide) - Setup instructions

---

**Happy content creating!** 🚀
