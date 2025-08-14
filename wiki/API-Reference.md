# API Reference

## Overview

The Thorium90 CMS provides a comprehensive REST API built with Laravel Sanctum for authentication. This API allows you to manage users, roles, permissions, pages, and settings programmatically.

## Base URL

```
https://yourdomain.com/api
```

## Authentication

The API uses Laravel Sanctum for authentication with support for:
- **Personal Access Tokens** - For server-to-server communication
- **SPA Authentication** - For single-page applications
- **Mobile Authentication** - For mobile applications

### Getting an API Token

#### 1. Personal Access Token

```bash
POST /api/auth/tokens
```

**Request Body:**
```json
{
    "email": "user@example.com",
    "password": "password",
    "token_name": "My API Token",
    "abilities": ["read", "write"]
}
```

**Response:**
```json
{
    "token": "1|abc123...",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com"
    }
}
```

#### 2. SPA Authentication

```bash
GET /sanctum/csrf-cookie
POST /api/auth/login
```

### Using the Token

Include the token in the Authorization header:

```bash
Authorization: Bearer 1|abc123...
```

## Rate Limiting

API requests are rate limited:
- **Authenticated users**: 60 requests per minute
- **Unauthenticated users**: 10 requests per minute
- **Token creation**: 5 requests per minute

## Response Format

All API responses follow a consistent format:

### Success Response
```json
{
    "success": true,
    "data": {
        // Response data
    },
    "message": "Operation completed successfully"
}
```

### Error Response
```json
{
    "success": false,
    "error": {
        "code": "VALIDATION_ERROR",
        "message": "The given data was invalid.",
        "details": {
            "email": ["The email field is required."]
        }
    }
}
```

### Pagination Response
```json
{
    "success": true,
    "data": [...],
    "meta": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 15,
        "total": 75
    },
    "links": {
        "first": "https://api.example.com/users?page=1",
        "last": "https://api.example.com/users?page=5",
        "prev": null,
        "next": "https://api.example.com/users?page=2"
    }
}
```

## Authentication Endpoints

### Login

```bash
POST /api/auth/login
```

**Request Body:**
```json
{
    "email": "user@example.com",
    "password": "password"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "user@example.com",
            "roles": ["Admin"],
            "permissions": ["view users", "create users"]
        }
    },
    "message": "Login successful"
}
```

### Logout

```bash
POST /api/auth/logout
```

**Headers:**
```
Authorization: Bearer {token}
```

### Get Current User

```bash
GET /api/auth/user
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com",
        "roles": ["Admin"],
        "permissions": ["view users", "create users"],
        "created_at": "2025-01-01T00:00:00Z"
    }
}
```

## User Management

### List Users

```bash
GET /api/users
```

**Query Parameters:**
- `page` (integer) - Page number
- `per_page` (integer) - Items per page (max 100)
- `search` (string) - Search by name or email
- `role` (string) - Filter by role
- `sort` (string) - Sort field (name, email, created_at)
- `order` (string) - Sort order (asc, desc)

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "roles": ["Admin"],
            "created_at": "2025-01-01T00:00:00Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "total": 25
    }
}
```

### Get User

```bash
GET /api/users/{id}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "roles": ["Admin"],
        "permissions": ["view users", "create users"],
        "created_at": "2025-01-01T00:00:00Z",
        "updated_at": "2025-01-01T00:00:00Z"
    }
}
```

### Create User

```bash
POST /api/users
```

**Request Body:**
```json
{
    "name": "Jane Doe",
    "email": "jane@example.com",
    "password": "secure-password",
    "roles": ["Editor"]
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 2,
        "name": "Jane Doe",
        "email": "jane@example.com",
        "roles": ["Editor"]
    },
    "message": "User created successfully"
}
```

### Update User

```bash
PUT /api/users/{id}
```

**Request Body:**
```json
{
    "name": "Jane Smith",
    "email": "jane.smith@example.com",
    "roles": ["Editor", "Author"]
}
```

### Delete User

```bash
DELETE /api/users/{id}
```

**Response:**
```json
{
    "success": true,
    "message": "User deleted successfully"
}
```

## Role Management

### List Roles

```bash
GET /api/roles
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Super Admin",
            "permissions": ["*"],
            "users_count": 1
        },
        {
            "id": 2,
            "name": "Admin",
            "permissions": ["view users", "create users"],
            "users_count": 5
        }
    ]
}
```

### Get Role

```bash
GET /api/roles/{id}
```

### Create Role

```bash
POST /api/roles
```

**Request Body:**
```json
{
    "name": "Content Manager",
    "permissions": ["view pages", "create pages", "edit pages"]
}
```

### Update Role

```bash
PUT /api/roles/{id}
```

### Delete Role

```bash
DELETE /api/roles/{id}
```

## Permission Management

### List Permissions

```bash
GET /api/permissions
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "view users",
            "category": "User Management"
        },
        {
            "id": 2,
            "name": "create users",
            "category": "User Management"
        }
    ]
}
```

### Assign Permission to Role

```bash
POST /api/roles/{roleId}/permissions
```

**Request Body:**
```json
{
    "permissions": ["view users", "create users"]
}
```

### Remove Permission from Role

```bash
DELETE /api/roles/{roleId}/permissions/{permissionId}
```

## Pages Management

### List Pages

```bash
GET /api/pages
```

**Query Parameters:**
- `status` (string) - Filter by status (draft, published, private)
- `author` (integer) - Filter by author ID
- `featured` (boolean) - Filter featured pages
- `search` (string) - Search in title and content

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "title": "Welcome to Thorium90",
            "slug": "welcome-to-thorium90",
            "status": "published",
            "is_featured": true,
            "author": {
                "id": 1,
                "name": "John Doe"
            },
            "published_at": "2025-01-01T00:00:00Z",
            "created_at": "2025-01-01T00:00:00Z"
        }
    ]
}
```

### Get Page

```bash
GET /api/pages/{id}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "title": "Welcome to Thorium90",
        "slug": "welcome-to-thorium90",
        "content": "Full page content...",
        "excerpt": "Page excerpt...",
        "status": "published",
        "is_featured": true,
        "meta_title": "SEO Title",
        "meta_description": "SEO Description",
        "meta_keywords": "cms, laravel, react",
        "schema_type": "Article",
        "schema_data": {},
        "author": {
            "id": 1,
            "name": "John Doe"
        },
        "published_at": "2025-01-01T00:00:00Z",
        "created_at": "2025-01-01T00:00:00Z",
        "updated_at": "2025-01-01T00:00:00Z"
    }
}
```

### Create Page

```bash
POST /api/pages
```

**Request Body:**
```json
{
    "title": "New Page",
    "slug": "new-page",
    "content": "Page content here...",
    "excerpt": "Brief description",
    "status": "draft",
    "is_featured": false,
    "meta_title": "SEO Title",
    "meta_description": "SEO Description",
    "meta_keywords": "keyword1, keyword2",
    "schema_type": "Article"
}
```

### Update Page

```bash
PUT /api/pages/{id}
```

### Delete Page

```bash
DELETE /api/pages/{id}
```

### Publish Page

```bash
POST /api/pages/{id}/publish
```

### Unpublish Page

```bash
POST /api/pages/{id}/unpublish
```

## Settings Management

### List Settings

```bash
GET /api/settings
```

**Query Parameters:**
- `category` (string) - Filter by category
- `public` (boolean) - Filter public settings only

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "key": "site_name",
            "value": "Thorium90 CMS",
            "type": "string",
            "category": "general",
            "description": "The name of your website",
            "is_public": true
        }
    ]
}
```

### Get Setting

```bash
GET /api/settings/{key}
```

### Update Setting

```bash
PUT /api/settings/{key}
```

**Request Body:**
```json
{
    "value": "New Site Name"
}
```

### Bulk Update Settings

```bash
POST /api/settings/bulk
```

**Request Body:**
```json
{
    "settings": {
        "site_name": "My Website",
        "site_description": "A great website",
        "timezone": "America/New_York"
    }
}
```

## File Upload

### Upload File

```bash
POST /api/upload
```

**Request Body (multipart/form-data):**
```
file: [binary file data]
folder: "pages" (optional)
```

**Response:**
```json
{
    "success": true,
    "data": {
        "url": "/storage/uploads/pages/image.jpg",
        "filename": "image.jpg",
        "size": 1024000,
        "mime_type": "image/jpeg"
    }
}
```

## Search

### Global Search

```bash
GET /api/search
```

**Query Parameters:**
- `q` (string, required) - Search query
- `type` (string) - Filter by type (users, pages, settings)
- `limit` (integer) - Limit results (max 50)

**Response:**
```json
{
    "success": true,
    "data": {
        "users": [
            {
                "id": 1,
                "name": "John Doe",
                "email": "john@example.com"
            }
        ],
        "pages": [
            {
                "id": 1,
                "title": "Welcome Page",
                "slug": "welcome"
            }
        ]
    }
}
```

## Webhooks

### List Webhooks

```bash
GET /api/webhooks
```

### Create Webhook

```bash
POST /api/webhooks
```

**Request Body:**
```json
{
    "url": "https://example.com/webhook",
    "events": ["user.created", "page.published"],
    "secret": "webhook-secret"
}
```

## Error Codes

| Code | Description |
|------|-------------|
| `VALIDATION_ERROR` | Request validation failed |
| `AUTHENTICATION_ERROR` | Authentication required |
| `AUTHORIZATION_ERROR` | Insufficient permissions |
| `NOT_FOUND` | Resource not found |
| `RATE_LIMIT_EXCEEDED` | Too many requests |
| `SERVER_ERROR` | Internal server error |

## SDKs and Libraries

### JavaScript/TypeScript

```bash
npm install @thorium90/api-client
```

```javascript
import { Thorium90Client } from '@thorium90/api-client';

const client = new Thorium90Client({
    baseURL: 'https://yourdomain.com/api',
    token: 'your-api-token'
});

// Get users
const users = await client.users.list();

// Create page
const page = await client.pages.create({
    title: 'New Page',
    content: 'Page content'
});
```

### PHP

```bash
composer require thorium90/api-client
```

```php
use Thorium90\ApiClient\Client;

$client = new Client([
    'base_url' => 'https://yourdomain.com/api',
    'token' => 'your-api-token'
]);

// Get users
$users = $client->users()->list();

// Create page
$page = $client->pages()->create([
    'title' => 'New Page',
    'content' => 'Page content'
]);
```

## Testing the API

### Using cURL

```bash
# Get users
curl -H "Authorization: Bearer your-token" \
     -H "Accept: application/json" \
     https://yourdomain.com/api/users

# Create page
curl -X POST \
     -H "Authorization: Bearer your-token" \
     -H "Content-Type: application/json" \
     -H "Accept: application/json" \
     -d '{"title":"Test Page","content":"Test content"}' \
     https://yourdomain.com/api/pages
```

### Using Postman

1. Import the Postman collection: [Download Collection](https://github.com/travissutphin/thorium90/blob/main/docs/postman/Thorium90-API.postman_collection.json)
2. Set up environment variables:
   - `base_url`: Your API base URL
   - `token`: Your API token

## Changelog

### v1.0.0
- Initial API release
- User management endpoints
- Role and permission management
- Pages CRUD operations
- Settings management
- File upload support

### v1.1.0 (Planned)
- Webhook support
- Advanced search
- Bulk operations
- API versioning
- GraphQL endpoint

## Support

For API support:
- **Documentation**: [API Reference](https://docs.thorium90.com/api)
- **Issues**: [GitHub Issues](https://github.com/travissutphin/thorium90/issues)
- **Discussions**: [GitHub Discussions](https://github.com/travissutphin/thorium90/discussions)
- **Email**: api-support@thorium90.com
