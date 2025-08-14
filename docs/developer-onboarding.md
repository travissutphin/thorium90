# Developer Onboarding Checklist

## 🚨 **MANDATORY: Development Workflow Completion Required**

**BEFORE writing ANY code or contributing to the project, you MUST complete the [Development Workflow](wiki/Development-Workflow.md) consistency check.**

## 📋 **Onboarding Checklist**

### **Phase 1: System Understanding (REQUIRED - 30 minutes)**

#### **1.1 Complete Development Workflow (25 minutes)**
- [ ] **Documentation Review** (5 minutes) - Read system overview and technical patterns
- [ ] **Code Pattern Analysis** (10 minutes) - Examine existing code for patterns
- [ ] **Permission & Role Analysis** (5 minutes) - Check existing permissions and roles
- [ ] **Database Schema Review** (5 minutes) - Understand data relationships
- [ ] **Consistency Validation** (5 minutes) - Verify alignment with existing standards

**If any boxes are unchecked, complete the [Development Workflow](wiki/Development-Workflow.md) first.**

#### **1.2 System Overview (5 minutes)**
- [ ] Read [wiki/Home.md](wiki/Home.md) - System goals and architecture
- [ ] Understand the Multi-Role User Authentication System purpose
- [ ] Familiarize with the technology stack (Laravel + React + Inertia.js)
- [ ] Review the 5 user roles and permission system

### **Phase 2: Technical Deep Dive (REQUIRED - 20 minutes)**

#### **2.1 Architecture Understanding (10 minutes)**
- [ ] Read [wiki/Developer-Guide.md](wiki/Developer-Guide.md) - Technical implementation details
- [ ] Understand the authentication components (Fortify, Sanctum, Socialite)
- [ ] Review the role-based access control (RBAC) system
- [ ] Familiarize with the database schema and relationships

#### **2.2 Code Patterns (10 minutes)**
- [ ] Review existing controllers in `app/Http/Controllers/`
- [ ] Examine model relationships in `app/Models/`
- [ ] Study React components in `resources/js/components/`
- [ ] Review testing patterns in `tests/Feature/`

### **Phase 3: Development Setup (REQUIRED - 15 minutes)**

#### **3.1 Environment Setup (10 minutes)**
- [ ] Clone the repository
- [ ] Install dependencies (`composer install && npm install`)
- [ ] Configure environment (`.env` file)
- [ ] Set up database and run migrations
- [ ] Start development servers

#### **3.2 Testing Setup (5 minutes)**
- [ ] Run the regression test suite (`./regression-test.sh`)
- [ ] Verify all tests pass
- [ ] Understand the testing workflow and patterns

### **Phase 4: First Contribution (REQUIRED - 30 minutes)**

#### **4.1 Feature Planning (15 minutes)**
- [ ] Use the [Task Request Template](wiki/Development-Workflow.md#task-request-template)
- [ ] Complete the consistency check for your feature
- [ ] Plan the implementation following existing patterns
- [ ] Identify required permissions and roles

#### **4.2 Implementation (15 minutes)**
- [ ] Create a feature branch
- [ ] Implement following established patterns
- [ ] Include proper authorization and testing
- [ ] Update relevant documentation

## 🚨 **Enforcement Mechanisms**

### **Code Review Requirements**
- [ ] Development Workflow completion verified
- [ ] Consistency check completed
- [ ] All required documentation updated
- [ ] Tests cover all user roles and permissions
- [ ] Follows existing code patterns

### **Quality Gates**
- [ ] All tests pass
- [ ] Code follows PSR-12 standards
- [ ] Proper authorization implemented
- [ ] Documentation updated
- [ ] Performance standards met

## 📚 **Essential Resources**

### **Required Reading (Start Here)**
1. **[Development Workflow](wiki/Development-Workflow.md)** - **MANDATORY: Consistency process**
2. **[System Overview](wiki/Home.md)** - Understanding the system goals
3. **[Developer Guide](wiki/Developer-Guide.md)** - Technical implementation details
4. **[Testing Strategy](wiki/Testing-Strategy.md)** - Testing procedures and standards

### **Quick References**
- **[Development Workflow Quick Reference](../DEVELOPMENT-WORKFLOW.md)** - Fast access to key points
- **[AI Development Guide](ai-development-guide.md)** - AI-specific consistency process
- **[Testing Quick Reference](../wiki/testing/TESTING-QUICK-REFERENCE.md)** - Testing procedures

## 🎯 **Success Criteria**

### **Onboarding Complete When**
- [ ] Development Workflow completed and understood
- [ ] System architecture and goals clear
- [ ] Code patterns and conventions familiar
- [ ] First contribution successfully implemented
- [ ] All tests passing
- [ ] Documentation updated

### **Ready for Development When**
- [ ] Can identify consistency issues before they occur
- [ ] Understands when to use each authentication component
- [ ] Can implement features following existing patterns
- [ ] Knows how to test all user roles and permissions
- [ ] Can update documentation appropriately

## 🔗 **Support and Help**

### **Getting Help**
- Review the [Development Workflow](wiki/Development-Workflow.md) first
- Check the [Troubleshooting](wiki/Troubleshooting.md) guide
- Review the [FAQ](wiki/FAQ.md) for common questions
- Ask questions in project discussions

### **Mentorship**
- Pair with experienced developers for first few features
- Review code with team members
- Participate in code reviews
- Share knowledge and learn from others

---

## 🚨 **IMPORTANT REMINDER**

**This consistency process is MANDATORY for all development work. Following it ensures your contributions align with the existing system architecture and goals, leading to a more maintainable and cohesive codebase.**

**Start here**: [wiki/Development-Workflow.md](wiki/Development-Workflow.md)

**Need help?** See the [AI Development Guide](ai-development-guide.md) for AI-specific guidance.
