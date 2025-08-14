# Development Workflow & Consistency Guide

## 🎯 **Mandatory Consistency Process for All Tasks**

### **Before Starting ANY Task**

Every developer (human or AI) MUST complete this consistency check before writing any code.

#### **Step 1: Documentation Review (5 minutes)**
```bash
# REQUIRED READING - Read these files in order:
1. wiki/Home.md → System overview and goals
2. wiki/Developer-Guide.md → Technical patterns  
3. wiki/Authentication-System-Summary.md → Auth architecture
4. wiki/Database-Schema.md → Data relationships
5. docs/README.md → Documentation structure
```

#### **Step 2: Code Pattern Analysis (10 minutes)**
```bash
# Examine existing code for patterns:
1. app/Http/Controllers/ → Controller structure
2. app/Models/ → Model relationships and traits
3. resources/js/components/ → React patterns
4. routes/ → Route organization
5. tests/Feature/ → Testing patterns
```

#### **Step 3: Permission & Role Analysis (5 minutes)**
```bash
# Check existing permissions and roles:
1. database/seeders/PermissionSeeder.php
2. database/seeders/RoleSeeder.php
3. app/Http/Middleware/ → Authorization patterns
```

#### **Step 4: Database Schema Review (5 minutes)**
```bash
# Understand data relationships:
1. wiki/Database-Schema.md
2. database/migrations/ → Table structure
3. app/Models/ → Model relationships
```

#### **Step 5: Consistency Validation (5 minutes)**
```bash
# Verify alignment with:
1. Existing naming conventions
2. Established code patterns
3. Testing standards
4. Documentation standards
```

**Total Time: 25 minutes**

---

## 📋 **Task Request Template**

### **Use This Template for Every Development Request**

```markdown
## 🎯 Task Request: [Feature Name]

### 📋 **Consistency Check (REQUIRED)**

#### 1. **System Goal Alignment**
- **Primary Goal**: Multi-Role User Authentication System
- **Secondary Goal**: Content Management with SEO/AEO/GEO
- **Tertiary Goal**: Robust testing and documentation

**Question**: How does this feature support these goals?
[Your answer here]

#### 2. **Architecture Compliance**
- **Backend Pattern**: Laravel controllers with proper authorization
- **Frontend Pattern**: React components with permission checking
- **Database Pattern**: Proper migrations with rollback support
- **Testing Pattern**: Feature tests with WithRoles trait

**Question**: Does this follow established patterns?
[Your answer here]

#### 3. **Permission & Role Integration**
- **Required Roles**: [List specific roles needed]
- **Required Permissions**: [List specific permissions needed]
- **Authorization Points**: [Where will permission checks occur?]

**Question**: How does this integrate with the existing RBAC system?
[Your answer here]

#### 4. **Existing Feature Analysis**
- **Similar Features**: [What existing features are similar?]
- **Reusable Components**: [What can I reuse?]
- **Database Tables**: [What tables will be affected?]

**Question**: What existing code should I reference?
[Your answer here]

### 🚀 **Feature Description**
[Describe what you want to implement]

### 🔧 **Technical Requirements**
[Specific technical needs]

### 📚 **Documentation Requirements**
[What docs need updating?]

### 🧪 **Testing Requirements**
[What tests need to be written?]
```

---

## ⚡ **Quick Consistency Checklist**

### **Before Every Request, Quickly Check:**

1. **✅ Does this support the Multi-Role Authentication goal?**
2. **✅ Does this use Laravel + React + Inertia.js?**
3. **✅ Does this follow existing code patterns?**
4. **✅ Does this integrate with the permission system?**
5. **✅ Does this include proper testing?**
6. **✅ Does this update relevant documentation?**

**If any answer is "No", complete the full consistency check above.**

---

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

---

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

---

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

---

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

---

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

---

## 🔄 **Phase 4: Monitoring and Iteration**

### **Continuous Improvement Process**

The Development Workflow is not static - it continuously improves through monitoring, feedback, and iteration.

#### **Weekly Metrics Collection**
- **Automated Collection**: GitHub Actions collect workflow metrics every Sunday
- **Metrics Tracked**: Completion rates, red flag detection, enforcement effectiveness
- **Storage**: Metrics stored in `workflow-metrics.json` for trend analysis

#### **Monthly Review Process**
- **Review Template**: Use [Monthly Workflow Review](../.github/ISSUE_TEMPLATE/monthly-workflow-review.md)
- **Analysis**: Review metrics, identify issues, plan improvements
- **Implementation**: Execute improvement plan over 6-week cycles
- **Documentation**: Update workflow based on feedback and insights

#### **Quarterly Major Updates**
- **Comprehensive Assessment**: Full workflow evaluation and major improvements
- **Developer Training**: Update onboarding materials and training processes
- **Process Refinement**: Major enforcement mechanism improvements
- **Success Measurement**: Assess improvement effectiveness

### **Feedback Collection Systems**

#### **Automated Feedback Requests**
- **PR Feedback**: Automatically request feedback on merged PRs
- **Issue Tracking**: Monitor feedback issues with `workflow-feedback` label
- **Survey System**: Quarterly developer satisfaction surveys
- **Sentiment Analysis**: Analyze feedback for trends and themes

#### **Feedback Integration**
- **Code Review Comments**: Track consistency-related feedback
- **Developer Surveys**: Regular workflow effectiveness assessments
- **AI Developer Feedback**: Monitor AI implementation quality
- **Pain Point Identification**: Systematic issue discovery and resolution

### **Success Metrics and Targets**

#### **Adoption Targets**
- **Workflow Completion Rate**: Target 95% (Current: [X]%)
- **Template Usage**: Target 90% (Current: [X]%)
- **Enforcement Effectiveness**: Target 95% (Current: [X]%)

#### **Quality Targets**
- **Code Review Iterations**: Target 80% reduction
- **Architectural Consistency**: Target 90% improvement
- **Permission Implementation**: Target 95% improvement

#### **Experience Targets**
- **Developer Satisfaction**: Target 8/10 (Current: [X]/10)
- **Onboarding Success**: Target 90% (Current: [X]%)
- **Process Clarity**: Target 9/10 (Current: [X]/10)

### **Implementation Tools**

#### **Monitoring Infrastructure**
- **GitHub Actions**: Automated metrics collection and reporting
- **Python Scripts**: Data analysis and trend identification
- **Issue Templates**: Standardized review and feedback processes
- **Automated Workflows**: Consistency enforcement and monitoring

#### **Analysis and Reporting**
- **Trend Analysis**: Identify patterns and improvement opportunities
- **Pattern Recognition**: Detect recurring issues and bottlenecks
- **Automated Reporting**: Weekly metrics reports and monthly summaries
- **Success Tracking**: Measure improvement effectiveness over time

---

## 🔗 **Resources and Support**

### **Essential Reading**
1. **[System Overview](Home.md)** - Understanding the system goals
2. **[Developer Guide](Developer-Guide.md)** - Technical implementation details
3. **[Testing Strategy](Testing-Strategy.md)** - Testing procedures and standards
4. **[Workflow Monitoring](docs/workflow-monitoring.md)** - Monitoring and iteration guide

### **Quick References**
- **[Development Workflow Quick Reference](../DEVELOPMENT-WORKFLOW.md)** - Fast access to key points
- **[AI Development Guide](docs/ai-development-guide.md)** - AI-specific consistency process
- **[Developer Onboarding](docs/developer-onboarding.md)** - New developer setup process

### **Support and Help**
- **Review the workflow first**: Most questions are answered in this document
- **Check the troubleshooting guide**: Common issues and solutions
- **Ask in project discussions**: Get help from the community
- **Create feedback issues**: Suggest improvements to the workflow

---

## 🚨 **IMPORTANT REMINDER**

**This consistency process is MANDATORY for all development work. Following it ensures your contributions align with the existing system architecture and goals, leading to a more maintainable and cohesive codebase.**

**The workflow continuously improves through your feedback and usage. Participate in the monthly reviews and provide feedback to help make it better for everyone.**

**Start here**: Complete the 5-step consistency check above before any development work.

**Need help?** See the [AI Development Guide](docs/ai-development-guide.md) for AI-specific guidance.
