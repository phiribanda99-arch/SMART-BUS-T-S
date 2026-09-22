# Contributing to SBMS

Thank you for your interest in contributing to the Smart Bus Management System (Zambia)! This document provides guidelines and instructions for contributing to the project.

## 🎯 Getting Started

### Prerequisites
- Git installed on your machine
- GitHub account
- Basic knowledge of HTML, CSS, JavaScript, and PHP
- MySQL for database work

### Fork and Clone
1. Fork the repository on GitHub
2. Clone your fork locally:
   ```bash
   git clone https://github.com/YOUR_USERNAME/SMART-BUS-MANAGEMENT-SYSTEM.git
   cd SMART-BUS-MANAGEMENT-SYSTEM
   ```
3. Add upstream remote:
   ```bash
   git remote add upstream https://github.com/NAKITA44/PUBLIC.git
   ```

## 🔄 Workflow

### Creating a Feature Branch
```bash
git checkout -b feature/your-feature-name
```

Branch naming conventions:
- Features: `feature/description` (e.g., `feature/add-sms-notifications`)
- Bug fixes: `bugfix/description` (e.g., `bugfix/fix-booking-crash`)
- Documentation: `docs/description` (e.g., `docs/api-endpoints`)

### Making Changes

1. **Code Style:**
   - Use consistent indentation (2 spaces for HTML/CSS, 4 spaces for PHP)
   - Use meaningful variable names
   - Add comments for complex logic
   - Follow existing code patterns

2. **Frontend Changes:**
   - Edit in `index.html` (passenger interface) or `admin.html` (admin interface)
   - Test in multiple browsers
   - Maintain responsive design
   - Keep glass-morphism design consistent

3. **Backend Changes:**
   - Edit in `api.php`
   - Test with multiple MySQL scenarios
   - Ensure CORS headers are correct
   - Add error handling

4. **Database Changes:**
   - Update `sbts_database.sql`
   - Test schema migrations
   - Document new tables/columns
   - Maintain foreign key relationships

### Committing Changes
```bash
git add .
git commit -m "feat: add new feature description"
```

Commit message format:
- `feat:` for new features
- `fix:` for bug fixes
- `docs:` for documentation updates
- `style:` for code style changes
- `refactor:` for code refactoring
- `test:` for test additions
- `chore:` for maintenance tasks

## ✅ Testing Your Changes

### Frontend Testing
1. Open `index.html` in browser
2. Test all user workflows
3. Verify responsive design on mobile
4. Test all admin functions in `admin.html`

### Backend Testing
1. Start local PHP server: `php -S localhost:8000`
2. Test API endpoints with Postman or curl
3. Verify database persistence
4. Test error handling

### Database Testing
1. Import schema: `mysql -u root < sbts_database.sql`
2. Verify all tables created
3. Test data integrity
4. Check foreign key constraints

## 🚀 Submitting a Pull Request

1. **Push to your fork:**
   ```bash
   git push origin feature/your-feature-name
   ```

2. **Create Pull Request:**
   - Go to the original repository
   - Click "New Pull Request"
   - Select your branch
   - Provide clear title and description

3. **PR Description Template:**
   ```markdown
   ## Description
   Brief description of changes

   ## Type of Change
   - [ ] New feature
   - [ ] Bug fix
   - [ ] Documentation update

   ## Related Issues
   Fixes #(issue number)

   ## Testing Done
   - [ ] Tested on Chrome
   - [ ] Tested on Firefox
   - [ ] Tested on Mobile
   - [ ] Database tested

   ## Screenshots (if applicable)
   [Add screenshots here]
   ```

## 📋 Review Process

1. **Code Review:**
   - Maintainers will review your code
   - Address feedback and comments
   - Make requested changes

2. **Approval:**
   - PR must be approved by at least one maintainer
   - All tests must pass
   - Code must follow project style

3. **Merge:**
   - PR will be merged to main branch
   - Your changes are now live!

## 🐛 Reporting Issues

Found a bug? Please create an issue with:
- Clear title summarizing the bug
- Step-by-step reproduction steps
- Expected behavior
- Actual behavior
- Screenshots/logs if applicable
- Your environment (OS, browser, PHP version)

## 📚 Code Guidelines

### JavaScript
```javascript
// Good - clear variable names
function calculateTotalRevenue(transactions) {
  return transactions
    .filter(t => t.status === 'Success')
    .reduce((sum, t) => sum + t.amount, 0);
}

// Bad - unclear variable names
function calc(t) {
  return t.filter(x => x.s === 'Success').reduce((s, i) => s + i.a, 0);
}
```

### PHP
```php
// Good - proper error handling
if ($conn->connect_error) {
  die(json_encode(['error' => 'Database connection failed']));
}

// Bad - no error handling
$conn->query($sql);
```

### CSS
```css
/* Good - consistent naming */
.btn-primary {
  background: var(--primary);
  color: white;
  padding: 12px 24px;
}

/* Bad - inconsistent */
.button1 {
  background: #667eea;
  color: #fff;
  padding: 12px 24px;
}
```

## 🤝 Community Guidelines

- Be respectful and professional
- Provide constructive feedback
- Ask questions if something is unclear
- Help other contributors
- Share knowledge and experience

## 📞 Getting Help

- Check existing issues and PRs
- Read code comments and documentation
- Ask in PR comments
- Contact maintainers

## 📄 License

By contributing, you agree that your contributions will be licensed under the same license as the project.

## 🎉 Thank You!

Your contributions help make SBTS better for everyone in Zambia and beyond. We appreciate your time and effort!

---

**Happy Contributing! 🚀**
