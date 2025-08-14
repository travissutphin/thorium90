# Pull Request

## 🚨 **MANDATORY: Development Workflow Consistency Check**

**BEFORE submitting this PR, you MUST complete the [Development Workflow](wiki/Development-Workflow.md) consistency check.**

### **Consistency Check Completion**
- [ ] **Documentation Review** - Read system overview and technical patterns
- [ ] **Code Pattern Analysis** - Examined existing code for patterns
- [ ] **Permission & Role Analysis** - Checked existing permissions and roles
- [ ] **Database Schema Review** - Understood data relationships
- [ ] **Consistency Validation** - Verified alignment with existing standards

**If any boxes are unchecked, complete the [Development Workflow](wiki/Development-Workflow.md) first.**

---

## 📋 **Feature Description**
[Describe what this PR implements]

## 🔧 **Technical Changes**
- [ ] Backend changes follow existing controller patterns
- [ ] Frontend changes use existing React patterns
- [ ] Database changes follow existing migration patterns
- [ ] Authorization implemented using Spatie Permission
- [ ] Tests cover all user roles and permission combinations

## 📚 **Documentation Updates**
- [ ] Updated relevant wiki pages
- [ ] Updated API documentation if applicable
- [ ] Updated testing documentation if applicable
- [ ] Updated user guides if applicable

## 🧪 **Testing**
- [ ] Feature tests created following existing patterns
- [ ] All user roles tested
- [ ] Permission combinations tested
- [ ] Regression tests pass (`./regression-test.sh`)
- [ ] Used `WithRoles` trait for authentication testing

## 🔒 **Security & Authorization**
- [ ] Proper permission checks implemented
- [ ] Middleware protection added where needed
- [ ] User input validation implemented
- [ ] No sensitive data exposed

## 📊 **Impact Assessment**
- [ ] Performance impact considered
- [ ] Database query optimization implemented
- [ ] Frontend render time optimized
- [ ] Error handling implemented

## 🔍 **Code Review Checklist**
- [ ] Follows existing naming conventions
- [ ] Uses established code patterns
- [ ] Implements proper error handling
- [ ] Includes appropriate logging
- [ ] Follows Laravel best practices
- [ ] Follows React best practices

---

## 📝 **Additional Notes**
[Any additional information for reviewers]

## 🔗 **Related Issues**
[Link to related issues or discussions]

---

**Remember**: This consistency process ensures your contributions align with the existing system architecture and goals, leading to a more maintainable and cohesive codebase.

**Need help?** See the [Development Workflow](wiki/Development-Workflow.md) for complete guidance.
