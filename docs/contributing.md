# Contributing Guide

Thank you for contributing to the Multi-Role User Authentication System! Your contributions help make this project better for everyone.

## 🚀 **Getting Started (REQUIRED READING)**

### **Step 1: Understand the System**
1. **[Home](Home)** - System overview and goals
2. **[Development Workflow](Development-Workflow)** - **MANDATORY: Consistency process**
3. **[Developer Guide](Developer-Guide)** - Technical implementation details
4. **[Authentication System Summary](Authentication-System-Summary)** - Auth architecture

### **Step 2: Follow the Workflow**
**BEFORE writing any code, you MUST complete the [Development Workflow](Development-Workflow) consistency check.**

This ensures your contributions align with the existing system architecture and goals.

## 🤝 How to Contribute

We welcome contributions from the community! There are many ways you can contribute:

### Types of Contributions

1. **Bug Reports**: Report bugs and issues you encounter
2. **Feature Requests**: Suggest new features and improvements
3. **Code Contributions**: Submit code fixes and enhancements
4. **Documentation**: Improve or add documentation
5. **Testing**: Help test the system and report issues
6. **Community Support**: Help other users in discussions

## 🐛 Reporting Bugs

### Before Reporting

1. **Check Existing Issues**: Search the [GitHub Issues](https://github.com/your-username/thorium90/issues) to see if the bug has already been reported
2. **Check Documentation**: Review the [Troubleshooting](Troubleshooting) guide and [FAQ](FAQ) for solutions
3. **Reproduce the Issue**: Make sure you can consistently reproduce the problem

### Bug Report Template

When reporting a bug, please include:

```markdown
## Bug Description
Brief description of the bug

## Steps to Reproduce
1. Go to '...'
2. Click on '...'
3. Scroll down to '...'
4. See error

## Expected Behavior
What you expected to happen

## Actual Behavior
What actually happened

## Environment
- **OS**: [e.g. Windows 10, macOS 12.0, Ubuntu 20.04]
- **PHP Version**: [e.g. 8.2.0]
- **Laravel Version**: [e.g. 11.0]
- **Database**: [e.g. MySQL 8.0, PostgreSQL 13]
- **Browser**: [e.g. Chrome 120, Firefox 115]

## Additional Information
- Screenshots (if applicable)
- Error messages
- Log files
- Any other relevant information
```

## 💡 Feature Requests

### Before Requesting

1. **Check Existing Features**: Make sure the feature doesn't already exist
2. **Search Discussions**: Check if the feature has been discussed before
3. **Consider Impact**: Think about how the feature would benefit the community

### Feature Request Template

```markdown
## Feature Description
Brief description of the feature you'd like to see

## Problem Statement
What problem does this feature solve?

## Proposed Solution
How would you like this feature to work?

## Use Cases
Describe specific scenarios where this feature would be useful

## Alternative Solutions
Are there any existing workarounds or alternatives?

## Additional Information
Any other relevant details, mockups, or examples
```

## 💻 Code Contributions

### Getting Started

1. **Fork the Repository**: Click the "Fork" button on GitHub
2. **Clone Your Fork**: 
   ```bash
   git clone https://github.com/your-username/thorium90.git
   cd thorium90
   ```
3. **Add Upstream Remote**:
   ```bash
   git remote add upstream https://github.com/original-username/thorium90.git
   ```

### Development Setup

1. **Install Dependencies**:
   ```bash
   composer install
   npm install
   ```

2. **Environment Setup**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database Setup**:
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

4. **Start Development Server**:
   ```bash
   php artisan serve
   npm run dev
   ```

### Coding Standards

#### PHP/Laravel Standards

1. **PSR-12 Coding Standards**: Follow PSR-12 for PHP code
2. **Laravel Conventions**: Follow Laravel naming conventions and best practices
3. **Documentation**: Add PHPDoc comments for classes and methods
4. **Type Hints**: Use type hints and return types where possible

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $users = User::with('roles')->paginate(15);
        
        return response()->json($users);
    }
}
```

#### JavaScript/React Standards

1. **ESLint Configuration**: Follow the project's ESLint rules
2. **TypeScript**: Use TypeScript for type safety
3. **Component Structure**: Follow React best practices
4. **Naming Conventions**: Use camelCase for variables and PascalCase for components

```tsx
import React, { useState, useEffect } from 'react';
import { usePage } from '@inertiajs/react';

interface User {
  id: number;
  name: string;
  email: string;
  roles: string[];
}

export default function UserList(): JSX.Element {
  const [users, setUsers] = useState<User[]>([]);
  
  useEffect(() => {
    // Fetch users
  }, []);
  
  return (
    <div className="user-list">
      {users.map((user) => (
        <UserCard key={user.id} user={user} />
      ))}
    </div>
  );
}
```

### Testing

1. **Write Tests**: Add tests for new features and bug fixes
2. **Run Tests**: Ensure all tests pass before submitting
3. **Test Coverage**: Aim for good test coverage

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=UserControllerTest

# Run with coverage
php artisan test --coverage
```

### Commit Guidelines

Follow conventional commit messages:

```
type(scope): description

feat(auth): add two-factor authentication
fix(permissions): resolve role assignment issue
docs(readme): update installation instructions
test(api): add user management tests
refactor(middleware): improve permission checking
```

Types:
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

### Pull Request Process

1. **Create Feature Branch**:
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. **Make Changes**: Implement your changes with proper tests

3. **Commit Changes**:
   ```bash
   git add .
   git commit -m "feat(auth): add new permission system"
   ```

4. **Push to Your Fork**:
   ```bash
   git push origin feature/your-feature-name
   ```

5. **Create Pull Request**: 
   - Go to your fork on GitHub
   - Click "New Pull Request"
   - Select your feature branch
   - Fill out the PR template

### Pull Request Template

```markdown
## Description
Brief description of the changes

## Type of Change
- [ ] Bug fix (non-breaking change which fixes an issue)
- [ ] New feature (non-breaking change which adds functionality)
- [ ] Breaking change (fix or feature that would cause existing functionality to not work as expected)
- [ ] Documentation update

## Testing
- [ ] My code follows the style guidelines of this project
- [ ] I have performed a self-review of my own code
- [ ] I have commented my code, particularly in hard-to-understand areas
- [ ] I have made corresponding changes to the documentation
- [ ] My changes generate no new warnings
- [ ] I have added tests that prove my fix is effective or that my feature works
- [ ] New and existing unit tests pass locally with my changes

## Checklist
- [ ] I have read the [Contributing Guide](Contributing-Guide)
- [ ] My code follows the coding standards
- [ ] I have added tests for my changes
- [ ] All tests pass
- [ ] I have updated the documentation

## Screenshots (if applicable)
Add screenshots to help explain your changes

## Additional Notes
Any additional information or context
```

## 📚 Documentation Contributions

### Documentation Standards

1. **Clear and Concise**: Write clear, easy-to-understand documentation
2. **Examples**: Include practical examples and code snippets
3. **Structure**: Follow the existing documentation structure
4. **Links**: Add relevant links to related documentation

### Documentation Areas

1. **Wiki Pages**: Update or add wiki pages
2. **Inline Documentation**: Improve code comments and PHPDoc
3. **README**: Update the main README file
4. **API Documentation**: Document API endpoints and responses

### Documentation Template

```markdown
# Page Title

Brief description of what this page covers.

## Section 1

Content for the first section.

### Subsection

More detailed content with examples:

```php
// Code example
$user = User::find(1);
$user->assignRole('Admin');
```

## Section 2

Content for the second section.

## Related Links

- [Related Page 1](link1)
- [Related Page 2](link2)
```

## 🧪 Testing Contributions

### Testing Guidelines

1. **Test Coverage**: Aim for high test coverage
2. **Test Types**: Write unit, feature, and integration tests
3. **Test Data**: Use factories and seeders for test data
4. **Test Isolation**: Ensure tests are independent

### Test Examples

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Traits\WithRoles;
use App\Models\User;

class UserManagementTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRolesAndPermissions();
    }

    public function test_admin_can_create_user(): void
    {
        $admin = $this->createAdmin();
        
        $response = $this->actingAs($admin)
            ->post('/users', [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'password123',
                'role' => 'Author',
            ]);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }
}
```

## 🔍 Code Review Process

### Review Guidelines

1. **Be Respectful**: Provide constructive feedback
2. **Be Specific**: Point out specific issues and suggest solutions
3. **Be Thorough**: Review code, tests, and documentation
4. **Be Timely**: Respond to PRs in a reasonable time

### Review Checklist

- [ ] Code follows project standards
- [ ] Tests are included and pass
- [ ] Documentation is updated
- [ ] No security vulnerabilities
- [ ] Performance considerations addressed
- [ ] Error handling is appropriate

## 🚀 Release Process

### Version Numbers

We follow [Semantic Versioning](https://semver.org/):

- **Major**: Breaking changes
- **Minor**: New features (backward compatible)
- **Patch**: Bug fixes (backward compatible)

### Release Checklist

- [ ] All tests pass
- [ ] Documentation is updated
- [ ] Changelog is updated
- [ ] Version number is updated
- [ ] Release notes are prepared

## 🏷️ Labels and Milestones

### Issue Labels

- `bug`: Something isn't working
- `enhancement`: New feature or request
- `documentation`: Improvements or additions to documentation
- `good first issue`: Good for newcomers
- `help wanted`: Extra attention is needed
- `invalid`: Something that isn't valid
- `question`: Further information is requested
- `wontfix`: This will not be worked on

### Pull Request Labels

- `ready for review`: Ready for code review
- `work in progress`: Still being worked on
- `needs review`: Requires review from maintainers
- `approved`: Approved by maintainers
- `changes requested`: Changes requested during review

## 📞 Getting Help

### Communication Channels

1. **GitHub Issues**: For bug reports and feature requests
2. **GitHub Discussions**: For questions and community support
3. **Pull Request Comments**: For code review discussions
4. **Email**: For sensitive or private matters

### Before Asking for Help

1. **Check Documentation**: Review the wiki and README
2. **Search Issues**: Look for similar issues or questions
3. **Provide Context**: Include relevant information and error messages
4. **Be Specific**: Describe exactly what you're trying to do

## 🎯 Contribution Ideas

### Good First Issues

- Fix typos in documentation
- Add missing tests
- Improve error messages
- Add examples to documentation
- Update dependencies

### Areas Needing Help

- Frontend components
- API endpoints
- Database optimizations
- Security improvements
- Performance enhancements
- Documentation improvements

## 📋 Contributor License Agreement

By contributing to this project, you agree that your contributions will be licensed under the same license as the project (MIT License).

## 🙏 Recognition

Contributors will be recognized in:

- Project README
- Release notes
- Contributor hall of fame
- GitHub contributors page

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](https://github.com/your-username/thorium90/blob/main/LICENSE) file for details.

---

Thank you for contributing to the Multi-Role User Authentication System! Your contributions help make this project better for everyone. 