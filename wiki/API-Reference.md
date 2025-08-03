# API Reference

This page provides comprehensive documentation for the Multi-Role User Authentication System API.

## 🔐 Authentication Endpoints

### Login
```http
POST /login
```

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password123",
  "remember": false
}
```

**Response:**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "roles": ["Admin"],
    "permissions": ["view-dashboard", "create-posts"],
    "is_admin": true,
    "is_content_creator": true
  }
}
```

### Logout
```http
POST /logout
```

**Response:**
```json
{
  "message": "Logged out successfully"
}
```

### Register
```http
POST /register
```

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response:**
```json
{
  "user": {
    "id": 2,
    "name": "John Doe",
    "email": "john@example.com",
    "roles": ["Subscriber"],
    "permissions": ["view-dashboard"],
    "is_admin": false,
    "is_content_creator": false
  }
}
```

## 👥 User Management API

### Get All Users
```http
GET /api/users
```

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 15)
- `search`: Search term for name or email
- `role`: Filter by role
- `permission`: Filter by permission

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "roles": ["Admin"],
      "permissions": ["view-dashboard", "create-posts"],
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 75
  }
}
```

### Get User
```http
GET /api/users/{id}
```

**Response:**
```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "roles": ["Admin"],
    "permissions": ["view-dashboard", "create-posts"],
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

### Create User
```http
POST /api/users
```

**Request Body:**
```json
{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "roles": ["Editor"]
}
```

**Response:**
```json
{
  "data": {
    "id": 3,
    "name": "Jane Doe",
    "email": "jane@example.com",
    "roles": ["Editor"],
    "permissions": ["view-dashboard", "create-posts", "edit-posts"],
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

### Update User
```http
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

**Response:**
```json
{
  "data": {
    "id": 3,
    "name": "Jane Smith",
    "email": "jane.smith@example.com",
    "roles": ["Editor", "Author"],
    "permissions": ["view-dashboard", "create-posts", "edit-posts", "edit-own-posts"],
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

### Delete User
```http
DELETE /api/users/{id}
```

**Response:**
```json
{
  "message": "User deleted successfully"
}
```

## 🏷️ Role Management API

### Get All Roles
```http
GET /api/roles
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Super Admin",
      "permissions": ["view-dashboard", "create-posts", "edit-posts", "delete-posts"],
      "users_count": 1,
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    },
    {
      "id": 2,
      "name": "Admin",
      "permissions": ["view-dashboard", "create-posts", "edit-posts"],
      "users_count": 3,
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    }
  ]
}
```

### Get Role
```http
GET /api/roles/{id}
```

**Response:**
```json
{
  "data": {
    "id": 1,
    "name": "Super Admin",
    "permissions": [
      {
        "id": 1,
        "name": "view-dashboard",
        "guard_name": "web"
      },
      {
        "id": 2,
        "name": "create-posts",
        "guard_name": "web"
      }
    ],
    "users": [
      {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
      }
    ],
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

### Create Role
```http
POST /api/roles
```

**Request Body:**
```json
{
  "name": "Moderator",
  "permissions": ["view-dashboard", "moderate-comments"]
}
```

**Response:**
```json
{
  "data": {
    "id": 6,
    "name": "Moderator",
    "permissions": ["view-dashboard", "moderate-comments"],
    "users_count": 0,
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

### Update Role
```http
PUT /api/roles/{id}
```

**Request Body:**
```json
{
  "name": "Senior Moderator",
  "permissions": ["view-dashboard", "moderate-comments", "delete-comments"]
}
```

**Response:**
```json
{
  "data": {
    "id": 6,
    "name": "Senior Moderator",
    "permissions": ["view-dashboard", "moderate-comments", "delete-comments"],
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

### Delete Role
```http
DELETE /api/roles/{id}
```

**Response:**
```json
{
  "message": "Role deleted successfully"
}
```

## 🔑 Permission Management API

### Get All Permissions
```http
GET /api/permissions
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "view-dashboard",
      "guard_name": "web",
      "roles_count": 5,
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    },
    {
      "id": 2,
      "name": "create-posts",
      "guard_name": "web",
      "roles_count": 3,
      "created_at": "2024-01-01T00:00:00.000000Z",
      "updated_at": "2024-01-01T00:00:00.000000Z"
    }
  ]
}
```

### Get Permission
```http
GET /api/permissions/{id}
```

**Response:**
```json
{
  "data": {
    "id": 1,
    "name": "view-dashboard",
    "guard_name": "web",
    "roles": [
      {
        "id": 1,
        "name": "Super Admin"
      },
      {
        "id": 2,
        "name": "Admin"
      }
    ],
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

### Create Permission
```http
POST /api/permissions
```

**Request Body:**
```json
{
  "name": "manage-reports",
  "guard_name": "web"
}
```

**Response:**
```json
{
  "data": {
    "id": 25,
    "name": "manage-reports",
    "guard_name": "web",
    "roles_count": 0,
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

### Update Permission
```http
PUT /api/permissions/{id}
```

**Request Body:**
```json
{
  "name": "view-reports",
  "guard_name": "web"
}
```

**Response:**
```json
{
  "data": {
    "id": 25,
    "name": "view-reports",
    "guard_name": "web",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

### Delete Permission
```http
DELETE /api/permissions/{id}
```

**Response:**
```json
{
  "message": "Permission deleted successfully"
}
```

## 🔍 Permission Checking API

### Check User Permissions
```http
POST /api/permissions/check
```

**Request Body:**
```json
{
  "user_id": 1,
  "permissions": ["create-posts", "edit-posts"]
}
```

**Response:**
```json
{
  "data": {
    "user_id": 1,
    "permissions": {
      "create-posts": true,
      "edit-posts": true
    },
    "all_permissions": ["view-dashboard", "create-posts", "edit-posts", "delete-posts"]
  }
}
```

### Check User Roles
```http
POST /api/roles/check
```

**Request Body:**
```json
{
  "user_id": 1,
  "roles": ["Admin", "Editor"]
}
```

**Response:**
```json
{
  "data": {
    "user_id": 1,
    "roles": {
      "Admin": true,
      "Editor": false
    },
    "all_roles": ["Super Admin", "Admin"]
  }
}
```

## 📊 Dashboard API

### Get Dashboard Statistics
```http
GET /api/dashboard/stats
```

**Response:**
```json
{
  "data": {
    "users": {
      "total": 150,
      "new_this_month": 25,
      "active_today": 45
    },
    "roles": {
      "total": 5,
      "most_popular": "Subscriber"
    },
    "permissions": {
      "total": 20,
      "most_used": "view-dashboard"
    },
    "system": {
      "version": "1.0.0",
      "last_backup": "2024-01-01T00:00:00.000000Z",
      "uptime": "99.9%"
    }
  }
}
```

### Get User Activity
```http
GET /api/dashboard/activity
```

**Query Parameters:**
- `user_id`: Filter by specific user
- `days`: Number of days to include (default: 7)

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "action": "login",
      "description": "User logged in",
      "ip_address": "192.168.1.1",
      "user_agent": "Mozilla/5.0...",
      "created_at": "2024-01-01T10:00:00.000000Z"
    },
    {
      "id": 2,
      "user_id": 1,
      "action": "create_post",
      "description": "Created new post: 'Getting Started'",
      "ip_address": "192.168.1.1",
      "user_agent": "Mozilla/5.0...",
      "created_at": "2024-01-01T11:00:00.000000Z"
    }
  ]
}
```

## 🔐 Authentication Middleware

### Protected Routes
All API endpoints (except login/register) require authentication:

```http
Authorization: Bearer {token}
```

### Role-Based Protection
Some endpoints require specific roles:

```http
X-Required-Role: Admin
```

### Permission-Based Protection
Some endpoints require specific permissions:

```http
X-Required-Permission: create-users
```

## 📝 Error Responses

### Authentication Errors
```json
{
  "error": "Unauthorized",
  "message": "Invalid credentials",
  "code": 401
}
```

### Authorization Errors
```json
{
  "error": "Forbidden",
  "message": "Insufficient permissions",
  "code": 403
}
```

### Validation Errors
```json
{
  "error": "Validation Error",
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  },
  "code": 422
}
```

### Not Found Errors
```json
{
  "error": "Not Found",
  "message": "User not found",
  "code": 404
}
```

### Server Errors
```json
{
  "error": "Internal Server Error",
  "message": "Something went wrong",
  "code": 500
}
```

## 🔄 Rate Limiting

### Default Limits
- **Authentication endpoints**: 5 requests per minute
- **API endpoints**: 60 requests per minute
- **Admin endpoints**: 30 requests per minute

### Rate Limit Headers
```http
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1640995200
```

### Rate Limit Exceeded
```json
{
  "error": "Too Many Requests",
  "message": "Rate limit exceeded",
  "code": 429
}
```

## 📊 Response Formats

### Standard Response
```json
{
  "data": {
    // Response data
  },
  "meta": {
    // Metadata (pagination, etc.)
  }
}
```

### Paginated Response
```json
{
  "data": [
    // Array of items
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 75,
    "from": 1,
    "to": 15
  },
  "links": {
    "first": "http://example.com/api/users?page=1",
    "last": "http://example.com/api/users?page=5",
    "prev": null,
    "next": "http://example.com/api/users?page=2"
  }
}
```

### Error Response
```json
{
  "error": "Error Type",
  "message": "Error message",
  "code": 400,
  "details": {
    // Additional error details
  }
}
```

## 🔧 SDK Examples

### PHP/Laravel
```php
use Illuminate\Support\Facades\Http;

// Get users
$response = Http::withToken($token)
    ->get('http://api.example.com/api/users');

$users = $response->json()['data'];

// Create user
$response = Http::withToken($token)
    ->post('http://api.example.com/api/users', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'roles' => ['Editor']
    ]);

$user = $response->json()['data'];
```

### JavaScript/Node.js
```javascript
const axios = require('axios');

// Get users
const response = await axios.get('http://api.example.com/api/users', {
  headers: {
    'Authorization': `Bearer ${token}`
  }
});

const users = response.data.data;

// Create user
const userResponse = await axios.post('http://api.example.com/api/users', {
  name: 'John Doe',
  email: 'john@example.com',
  password: 'password123',
  roles: ['Editor']
}, {
  headers: {
    'Authorization': `Bearer ${token}`
  }
});

const user = userResponse.data.data;
```

### Python
```python
import requests

# Get users
response = requests.get(
    'http://api.example.com/api/users',
    headers={'Authorization': f'Bearer {token}'}
)

users = response.json()['data']

# Create user
user_response = requests.post(
    'http://api.example.com/api/users',
    json={
        'name': 'John Doe',
        'email': 'john@example.com',
        'password': 'password123',
        'roles': ['Editor']
    },
    headers={'Authorization': f'Bearer {token}'}
)

user = user_response.json()['data']
```

## 📚 Additional Resources

### API Documentation
- **OpenAPI/Swagger**: Available at `/api/documentation`
- **Postman Collection**: Download from repository
- **Insomnia Collection**: Available in repository

### Testing
- **API Tests**: Run with `php artisan test --filter=ApiTest`
- **Postman Tests**: Automated tests in collection
- **Mock Server**: Available for development

### Support
- **API Issues**: Report on GitHub Issues
- **Documentation**: Check wiki pages
- **Community**: Join GitHub Discussions

---

**Need help with the API?** Check our [Developer Guide](Developer-Guide) or [Contact Support](Support) for assistance. 