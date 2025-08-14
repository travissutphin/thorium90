# Development Workflow - Quick Reference

## 🚨 **MANDATORY: Complete This Process Before ANY Development**

**This is a quick reference. For the complete process, see [wiki/Development-Workflow.md](wiki/Development-Workflow.md)**

## ⚡ **Quick Consistency Checklist (2 minutes)**

Before every request, quickly check:

1. **✅ Does this support the Multi-Role Authentication goal?**
2. **✅ Does this use Laravel + React + Inertia.js?**
3. **✅ Does this follow existing code patterns?**
4. **✅ Does this integrate with the permission system?**
5. **✅ Does this include proper testing?**
6. **✅ Does this update relevant documentation?**

## 🔍 **5-Step Consistency Process (25 minutes total)**

### **Step 1: Documentation Review (5 minutes)**
```bash
# REQUIRED READING - Read these files in order:
1. wiki/Home.md → System overview and goals
2. wiki/Developer-Guide.md → Technical patterns  
3. wiki/Authentication-System-Summary.md → Auth architecture
4. wiki/Database-Schema.md → Data relationships
5. docs/README.md → Documentation structure
```

### **Step 2: Code Pattern Analysis (10 minutes)**
```bash
# Examine existing code for patterns:
1. app/Http/Controllers/ → Controller structure
2. app/Models/ → Model relationships and traits
3. resources/js/components/ → React patterns
4. routes/ → Route organization
5. tests/Feature/ → Testing patterns
```

### **Step 3: Permission & Role Analysis (5 minutes)**
```bash
# Check existing permissions and roles:
1. database/seeders/PermissionSeeder.php
2. database/seeders/RoleSeeder.php
3. app/Http/Middleware/ → Authorization patterns
```

### **Step 4: Database Schema Review (5 minutes)**
```bash
# Understand data relationships:
1. wiki/Database-Schema.md
2. database/migrations/ → Table structure
3. app/Models/ → Model relationships
```

### **Step 5: Consistency Validation (5 minutes)**
```bash
# Verify alignment with:
1. Existing naming conventions
2. Established code patterns
3. Testing standards
4. Documentation standards
```

## 🚨 **Red Flags (STOP if you see these)**

- ❌ "This doesn't need user roles"
- ❌ "We don't need to test this"
- ❌ "Let's use a different framework"
- ❌ "Documentation isn't important"
- ❌ "Let's skip the authorization checks"

## 📋 **Task Request Template**

Use this template for EVERY task request:

```markdown
## 🎯 Task Request: [Feature Name]

### 📋 **Consistency Check (REQUIRED)**

#### 1. **System Goal Alignment**
- **Primary Goal**: Multi-Role User Authentication System
- **Secondary Goal**: Content Management with SEO/AEO/GEO
- **Tertiary Goal**: Robust testing and documentation

**Question**: How does this feature support these goals?

#### 2. **Architecture Compliance**
- **Backend Pattern**: Laravel controllers with proper authorization
- **Frontend Pattern**: React components with permission checking
- **Database Pattern**: Proper migrations with rollback support
- **Testing Pattern**: Feature tests with WithRoles trait

**Question**: Does this follow established patterns?

#### 3. **Permission & Role Integration**
- **Required Roles**: [List specific roles needed]
- **Required Permissions**: [List specific permissions needed]
- **Authorization Points**: [Where will permission checks occur?]

**Question**: How does this integrate with the existing RBAC system?

#### 4. **Existing Feature Analysis**
- **Similar Features**: [What existing features are similar?]
- **Reusable Components**: [What can I reuse?]
- **Database Tables**: [What tables will be affected?]

**Question**: What existing code should I reference?

### 🚀 **Feature Description**
[Describe what you want to implement]

### 🔧 **Technical Requirements**
[Specific technical needs]

### 📚 **Documentation Requirements**
[What docs need updating?]

### 🧪 **Testing Requirements**
[What tests need to be written?]
```

## 🔗 **Full Documentation**

- **[Complete Development Workflow](wiki/Development-Workflow.md)** - Full process with examples
- **[System Overview](wiki/Home.md)** - System goals and architecture
- **[Developer Guide](wiki/Developer-Guide.md)** - Technical implementation details
- **[Testing Strategy](wiki/Testing-Strategy.md)** - Testing procedures and standards

---

**Remember**: This consistency process is MANDATORY for all development tasks. Following it ensures your contributions align with the existing system architecture and goals, leading to a more maintainable and cohesive codebase.

**Start here**: [wiki/Development-Workflow.md](wiki/Development-Workflow.md)
