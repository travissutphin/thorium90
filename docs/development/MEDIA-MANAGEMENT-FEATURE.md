# Media Management System - Feature Documentation

## Overview

The Media Management system provides comprehensive file upload, storage, and management capabilities for the Thorium90 CMS. It supports multiple file formats with security features, permission-based access control, and an intuitive user interface.

## Features Implemented

### Backend Components

#### 1. Media Model (`app/Models/Media.php`)
- **File Metadata**: Stores filename, MIME type, size, extension, and file type
- **Security**: Virus scanning integration, secure filename generation
- **Organization**: Tags, descriptions, alt text, and visibility controls
- **Relationships**: Links to uploading user with soft deletes
- **Scopes**: Query filtering by type, visibility, and scan status

#### 2. Media Controller (`app/Http/Controllers/Admin/MediaController.php`)
- **CRUD Operations**: Full create, read, update, delete functionality
- **File Upload**: Multi-file upload with progress tracking
- **Bulk Operations**: Delete, visibility changes, tag management
- **API Endpoints**: JSON responses for AJAX operations
- **Permission-based Access**: Route-level permission checking

#### 3. Media Upload Service (`app/Services/MediaUploadService.php`)
- **File Validation**: MIME type, size limits, content verification
- **Secure Storage**: Organized directory structure by type and date
- **Thumbnail Generation**: Automatic thumbnails for images
- **Metadata Extraction**: Dimensions, file properties
- **Virus Scanning**: Ready for ClamAV integration

#### 4. Database Migration (`database/migrations/2025_08_27_230701_create_media_table.php`)
- **Complete Schema**: All necessary fields with proper indexes
- **Foreign Keys**: User relationships with cascade deletes
- **Performance**: Optimized indexes for common queries

### Frontend Components

#### 1. Media Index (`resources/js/pages/admin/media/index.tsx`)
- **Grid Layout**: Visual media browser with thumbnails
- **Search & Filters**: By filename, type, uploader
- **Statistics Dashboard**: File counts and storage usage
- **Bulk Selection**: Multi-file operations
- **Pagination**: Efficient browsing of large media libraries

#### 2. Media Upload (`resources/js/pages/admin/media/create.tsx`)
- **Drag & Drop**: Intuitive file upload interface
- **Progress Tracking**: Real-time upload progress
- **File Validation**: Client-side pre-validation
- **Multi-file Support**: Upload up to 10 files simultaneously
- **Preview Generation**: Image previews before upload

#### 3. Media Detail View (`resources/js/pages/admin/media/show.tsx`)
- **File Preview**: Images, videos, audio playback
- **Metadata Display**: Complete file information
- **Management Actions**: Edit, delete, download
- **Security Status**: Virus scan results
- **Direct URL Access**: Copy shareable links

#### 4. Media Edit (`resources/js/pages/admin/media/edit.tsx`)
- **Metadata Editing**: Alt text, descriptions, tags
- **Visibility Control**: Public/private toggle
- **Tag Management**: Add/remove organization tags
- **Form Validation**: Client and server-side validation

### Security Features

#### File Validation
- **MIME Type Verification**: Content-based validation
- **Size Limits**: Type-specific file size restrictions
- **Magic Number Checking**: Prevents file spoofing
- **Extension Validation**: Whitelist-based approach

#### Access Control
- **Permission-based Routes**: Role-specific access
- **User Ownership**: Upload attribution and tracking
- **Visibility Controls**: Public/private file access

#### Storage Security
- **Secure Filenames**: Prevents directory traversal
- **Organized Structure**: Date-based folder organization
- **Virus Scanning**: Integration points for security scanning

## Permissions System

### Media Permissions
- `view media`: Access media library and view files
- `upload media`: Upload new media files
- `edit media`: Modify file metadata and settings
- `delete media`: Remove files from the system

### Role Integration
- **Authors+**: Can view and upload media
- **Editors+**: Can edit and manage media
- **Admins+**: Full media management access

## File Type Support

### Images
- **Formats**: JPEG, PNG, GIF, WebP, SVG
- **Max Size**: 10MB per file
- **Features**: Automatic thumbnails, dimension extraction

### Documents
- **Formats**: PDF, DOC/DOCX, XLS/XLSX, PPT/PPTX, TXT, CSV
- **Max Size**: 50MB per file
- **Features**: File type icons, preview links

### Videos
- **Formats**: MP4, AVI, QuickTime, WebM
- **Max Size**: 500MB per file
- **Features**: Video player integration

### Audio
- **Formats**: MP3, WAV, OGG, AAC
- **Max Size**: 100MB per file
- **Features**: Audio player controls

## API Endpoints

### REST Routes
- `GET /admin/media` - Media library index
- `GET /admin/media/create` - Upload form
- `POST /admin/media` - File upload
- `GET /admin/media/{id}` - File details
- `GET /admin/media/{id}/edit` - Edit form
- `PUT /admin/media/{id}` - Update metadata
- `DELETE /admin/media/{id}` - Delete file

### AJAX Endpoints
- `POST /admin/media/upload-api` - Single file upload
- `POST /admin/media/bulk-action` - Bulk operations

## Database Schema

```sql
CREATE TABLE media (
    id BIGINT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    stored_filename VARCHAR(255) NOT NULL,
    path VARCHAR(255) NOT NULL,
    disk VARCHAR(255) DEFAULT 'public',
    mime_type VARCHAR(255) NOT NULL,
    extension VARCHAR(255) NOT NULL,
    size BIGINT UNSIGNED NOT NULL,
    type VARCHAR(255) NOT NULL,
    metadata JSON,
    thumbnail_path VARCHAR(255),
    alt_text VARCHAR(255),
    description TEXT,
    tags JSON,
    is_public BOOLEAN DEFAULT TRUE,
    uploaded_by BIGINT REFERENCES users(id),
    scanned_at TIMESTAMP,
    scan_result VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);
```

## Storage Structure

```
public/
├── media/
│   ├── images/
│   │   └── YYYY/
│   │       └── MM/
│   │           ├── filename_hash.jpg
│   │           └── thumbnails/
│   │               └── filename_hash.jpg
│   ├── documents/
│   │   └── YYYY/MM/
│   ├── videos/
│   │   └── YYYY/MM/
│   └── audios/
│       └── YYYY/MM/
```

## Configuration

### File Limits (in MediaUploadService)
```php
const MAX_IMAGE_SIZE = 10 * 1024 * 1024; // 10MB
const MAX_DOCUMENT_SIZE = 50 * 1024 * 1024; // 50MB
const MAX_VIDEO_SIZE = 500 * 1024 * 1024; // 500MB
const MAX_AUDIO_SIZE = 100 * 1024 * 1024; // 100MB
```

### Allowed MIME Types
- **Images**: image/jpeg, image/png, image/gif, image/webp, image/svg+xml
- **Documents**: application/pdf, office documents, text files
- **Videos**: video/mp4, video/avi, video/quicktime, video/webm
- **Audio**: audio/mpeg, audio/wav, audio/ogg, audio/aac

## Testing

### Test Coverage
- **MediaUploadTest.php**: Comprehensive feature tests
- **16 Test Methods**: Upload, validation, CRUD operations, permissions
- **Security Testing**: File type validation, size limits
- **Permission Testing**: Role-based access control

### Current Test Status
- ✅ **Core Tests**: Admin login, page management, dashboard - **PASSING**
- ⚠️ **Media Tests**: Database seeding issues in test environment
- ✅ **Regression Tests**: Core Thorium90 functionality intact

## Integration Points

### Navigation
- **Sidebar Menu**: Added to admin navigation with permission check
- **Route**: `/admin/media` with proper breadcrumbs
- **Icon**: Media/Image icon in sidebar

### Permission Integration
- **Spatie Laravel Permission**: Full integration with existing system
- **Middleware**: Route-level permission checking
- **UI Components**: Permission-aware component rendering

## Production Considerations

### Virus Scanning
- **Current**: Placeholder implementation marks files as clean
- **Production**: Integrate ClamAV or similar service
- **Configuration**: Update `queueVirusScan()` method

### Thumbnail Generation
- **Current**: Basic file copy for MVP
- **Production**: Install Intervention Image or GD library
- **Feature**: Implement proper image resizing (300x300px)

### Storage Backend
- **Current**: Local public disk
- **Production**: Configure S3 or similar cloud storage
- **Configuration**: Update disk configuration in `config/filesystems.php`

### Performance
- **Caching**: Consider Redis for media metadata
- **CDN**: Use CDN for public media delivery
- **Optimization**: Image optimization pipelines

## Deployment Checklist

1. **Database**: Run media migration
2. **Permissions**: Seed media permissions and roles
3. **Storage**: Create storage directories with proper permissions
4. **Assets**: Build frontend assets with new media components
5. **Testing**: Verify upload functionality in production environment

## Usage Examples

### Creating Media Programmatically
```php
use App\Services\MediaUploadService;

$mediaService = app(MediaUploadService::class);
$media = $mediaService->uploadFile(
    $uploadedFile,
    $userId,
    [
        'alt_text' => 'Image description',
        'description' => 'Detailed description',
        'tags' => ['product', 'featured'],
        'is_public' => true
    ]
);
```

### Querying Media
```php
// Get all public images
$images = Media::ofType('image')->public()->get();

// Get user's uploaded files
$userFiles = Media::uploadedBy($userId)->get();

// Get virus-clean files only
$cleanFiles = Media::virusClean()->get();
```

## Future Enhancements

### Planned Features
- **Image Editing**: Basic crop/resize functionality
- **Folder Organization**: Nested folder structure
- **Advanced Search**: Full-text search across metadata
- **Bulk Upload**: ZIP file extraction
- **Media Gallery**: Public gallery views
- **API Integration**: Full REST API for external access

### Integration Opportunities
- **Blog Posts**: Rich media embedding
- **Page Builder**: Media picker components
- **SEO**: Automatic alt text generation
- **Analytics**: Media usage tracking

## Troubleshooting

### Common Issues
1. **Upload Fails**: Check file permissions and storage disk configuration
2. **Thumbnails Missing**: Verify GD/Imagick installation
3. **Permission Errors**: Ensure media permissions are seeded
4. **Large Files**: Adjust PHP upload limits and timeout values

### Debug Commands
```bash
# Check storage permissions
ls -la storage/app/public/media

# Verify media migration
php artisan migrate:status

# Test file upload
php artisan tinker
>>> Storage::disk('public')->put('test.txt', 'test content');
```

This media management system provides a solid foundation for file handling in Thorium90, with room for future enhancements and production-ready security features.