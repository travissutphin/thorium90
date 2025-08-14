# AI Development Guide

## 🚨 **MANDATORY: Development Workflow Consistency Process**

**BEFORE writing ANY code or providing ANY implementation, you MUST complete the [Development Workflow](wiki/Development-Workflow.md) consistency check.**

## 🎯 **AI Developer Instructions**

### **Step 1: Complete the Consistency Check (25 minutes)**

You MUST complete these 5 steps before proceeding:

#### **1. Documentation Review (5 minutes)**
```bash
# REQUIRED READING - Read these files in order:
1. wiki/Home.md → System overview and goals
2. wiki/Developer-Guide.md → Technical patterns  
3. wiki/Authentication-System-Summary.md → Auth architecture
4. wiki/Database-Schema.md → Data relationships
5. docs/README.md → Documentation structure
```

#### **2. Code Pattern Analysis (10 minutes)**
```bash
# Examine existing code for patterns:
1. app/Http/Controllers/ → Controller structure
2. app/Models/ → Model relationships and traits
3. resources/js/components/ → React patterns
4. routes/ → Route organization
5. tests/Feature/ → Testing patterns
```

#### **3. Permission & Role Analysis (5 minutes)**
```bash
# Check existing permissions and roles:
1. database/seeders/PermissionSeeder.php
2. database/seeders/RoleSeeder.php
3. app/Http/Middleware/ → Authorization patterns
```

#### **4. Database Schema Review (5 minutes)**
```bash
# Understand data relationships:
1. wiki/Database-Schema.md
2. database/migrations/ → Table structure
3. app/Models/ → Model relationships
```

#### **5. Consistency Validation (5 minutes)**
```bash
# Verify alignment with:
1. Existing naming conventions
2. Established code patterns
3. Testing standards
4. Documentation standards
```

### **Step 2: Use the Task Request Template**

For every development request, use this template:

```markdown
## 🎯 Task Request: [Feature Name]

### 📋 **Consistency Check (REQUIRED)**

#### 1. **System Goal Alignment**
- **Primary Goal**: Multi-Role User Authentication System
- **Secondary Goal**: Content Management with SEO/AEO/GEO
- **Tertiary Goal**: Robust testing and documentation

**Question**: How does this feature support these goals?
[Your analysis based on the documentation review]

#### 2. **Architecture Compliance**
- **Backend Pattern**: Laravel controllers with proper authorization
- **Frontend Pattern**: React components with permission checking
- **Database Pattern**: Proper migrations with rollback support
- **Testing Pattern**: Feature tests with WithRoles trait

**Question**: Does this follow established patterns?
[Your analysis based on the code pattern review]

#### 3. **Permission & Role Integration**
- **Required Roles**: [List specific roles needed]
- **Required Permissions**: [List specific permissions needed]
- **Authorization Points**: [Where will permission checks occur?]

**Question**: How does this integrate with the existing RBAC system?
[Your analysis based on the permission review]

#### 4. **Existing Feature Analysis**
- **Similar Features**: [What existing features are similar?]
- **Reusable Components**: [What can I reuse?]
- **Database Tables**: [What tables will be affected?]

**Question**: What existing code should I reference?
[Your analysis based on the code review]

### 🚀 **Feature Description**
[Describe what you want to implement]

### 🔧 **Technical Requirements**
[Specific technical needs]

### 📚 **Documentation Requirements**
[What docs need updating?]

### 🧪 **Testing Requirements**
[What tests need to be written?]
```

## 🚨 **Red Flags That Indicate Inconsistency**

### **Architecture Red Flags**
- ❌ Requesting features that don't use authentication
- ❌ Asking for different frontend frameworks
- ❌ Requesting different database systems
- ❌ Asking for different testing frameworks

### **Pattern Red Flags**
- ❌ "Can you create a new way to handle permissions?"
- ❌ "Let's use a different controller structure"
- ❌ "Can we change how React components work?"
- ❌ "Let's use different database naming conventions"

### **Goal Red Flags**
- ❌ "This doesn't need user roles"
- ❌ "We don't need to test this"
- ❌ "Documentation isn't important for this feature"
- ❌ "Let's skip the authorization checks"

## 🔧 **Implementation Requirements**

### **Backend Implementation**
- ✅ Use existing controller patterns from `app/Http/Controllers/`
- ✅ Implement proper authorization using Spatie Permission
- ✅ Follow Laravel naming conventions and best practices
- ✅ Use existing middleware patterns
- ✅ Include proper error handling and validation

### **Frontend Implementation**
- ✅ Use existing React patterns from `resources/js/components/`
- ✅ Implement permission checking using `usePermissions()` hook
- ✅ Follow existing UI component patterns
- ✅ Use existing layouts from `resources/js/layouts/`
- ✅ Include proper error boundaries and loading states

### **Database Implementation**
- ✅ Follow existing migration patterns
- ✅ Use proper foreign key relationships
- ✅ Include soft deletes where appropriate
- ✅ Follow existing table naming conventions
- ✅ Include proper indexes for performance

### **Testing Implementation**
- ✅ Use `WithRoles` trait for authentication testing
- ✅ Test all user roles and permission combinations
- ✅ Follow existing test patterns from `tests/Feature/`
- ✅ Include regression tests
- ✅ Test authorization and security aspects

## 📚 **Documentation Requirements**

### **Code Documentation**
- ✅ Add PHPDoc comments for classes and methods
- ✅ Document permission requirements
- ✅ Include usage examples
- ✅ Document any new patterns or conventions

### **User Documentation**
- ✅ Update relevant wiki pages
- ✅ Update API documentation if applicable
- ✅ Update testing documentation if applicable
- ✅ Include screenshots or examples

## 🧪 **Testing Requirements**

### **Test Coverage**
- ✅ Unit tests for new functionality
- ✅ Feature tests for all user roles
- ✅ Permission-based access tests
- ✅ Database relationship tests
- ✅ Frontend component tests

### **Test Patterns**
- ✅ Use existing test factories and seeders
- ✅ Follow existing test naming conventions
- ✅ Include proper test data setup
- ✅ Test edge cases and error conditions

## 🎯 **Quality Standards**

### **Code Quality**
- ✅ Follow PSR-12 coding standards
- ✅ Use type hints and return types
- ✅ Implement proper error handling
- ✅ Include appropriate logging
- ✅ Follow Laravel and React best practices

### **Performance Standards**
- ✅ Authentication response time: < 200ms
- ✅ Permission check: < 10ms
- ✅ Database queries: < 50ms
- ✅ Frontend render time: < 100ms

### **Security Standards**
- ✅ Proper permission validation
- ✅ Input sanitization and validation
- ✅ CSRF protection
- ✅ SQL injection prevention
- ✅ XSS prevention

## 🔗 **Resources**

### **Essential Reading**
1. **[Development Workflow](wiki/Development-Workflow.md)** - **MANDATORY: Start here**
2. **[System Overview](wiki/Home.md)** - Understanding the system goals
3. **[Developer Guide](wiki/Developer-Guide.md)** - Technical implementation details
4. **[Testing Strategy](wiki/Testing-Strategy.md)** - Testing procedures and standards

### **Code References**
- [Existing Controllers](app/Http/Controllers/) - Controller patterns
- [Existing Models](app/Models/) - Model patterns
- [Existing Components](resources/js/components/) - React patterns
- [Existing Tests](tests/Feature/) - Testing patterns

---

**Remember**: This consistency process is MANDATORY for all AI development tasks. Following it ensures your implementations align with the existing system architecture and goals, leading to a more maintainable and cohesive codebase.

**Start here**: [wiki/Development-Workflow.md](wiki/Development-Workflow.md)
