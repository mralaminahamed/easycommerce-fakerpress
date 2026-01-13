# Git Commit Instructions for easycommerce-fakerpress Plugin

## Overview

This plugin uses [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/) to maintain a clear and consistent commit history. All commits must follow the specified format to enable automated changelog generation and semantic versioning.

## Commit Message Format

```
type(scope): short description

[optional body]

[optional footer]
```

### Required Elements

- **type**: The category of change (see Types section below)
- **scope**: The area of the codebase affected (optional, but recommended)
- **description**: Brief, imperative description of the change

### Types

| Type       | Description                                   | Release Impact     |
| ---------- | --------------------------------------------- | ------------------ |
| `feat`     | New feature for users                         | Minor version bump |
| `fix`      | Bug fix for users                             | Patch version bump |
| `docs`     | Documentation changes                         | No version bump    |
| `style`    | Code style changes (formatting, etc.)         | No version bump    |
| `refactor` | Code restructuring without functional changes | No version bump    |
| `perf`     | Performance improvements                      | Patch version bump |
| `test`     | Adding/updating tests                         | No version bump    |
| `chore`    | Maintenance tasks (build, dependencies)       | No version bump    |
| `ci`       | CI/CD pipeline changes                        | No version bump    |
| `build`    | Build system changes                          | No version bump    |

### Scope Guidelines

Use specific, meaningful scopes that clearly identify the affected area:

**Common Scopes:**

- `admin` - Admin interface components
- `api` - REST API endpoints
- `generator` - Data generation logic
- `product`, `customer`, `order`, `coupon` - Specific generator types
- `ui` - User interface components
- `docs` - Documentation files
- `build` - Build system and tooling
- `deps` - Dependency updates

**Examples:**

```
feat(product): add support for variable products
fix(order): resolve tax calculation error
docs(readme): update installation instructions
chore(deps): update Tailwind CSS to v4
```

## Detailed Examples

### Feature Commits

```
feat(customer): add demographic-based customer generation

- Add age distribution parameters
- Include geographic weighting
- Support multiple locales for names

Closes #123
```

### Bug Fix Commits

```
fix(order): correct order total calculation

Order totals were incorrectly calculated when discounts
were applied after shipping costs.

Fixes order total mismatch in checkout flow.
```

### Documentation Commits

```
docs(readme): update Tailwind v4 migration guide

- Add Node.js 20+ requirement
- Include PostCSS configuration steps
- Document @theme and @utility directives
```

### Refactoring Commits

```
refactor(generator): extract common validation logic

Move shared parameter validation into base class
to reduce code duplication across generators.
```

## Best Practices

### Commit Size

- **Small & Focused**: Each commit should address one logical change
- **Atomic**: Changes should be complete and independently reviewable
- **Tested**: Verify functionality before committing
- **No Mixed Concerns**: Don't combine features, fixes, and refactoring

### Description Guidelines

- Use imperative mood: "add", "fix", "update", not "added", "fixed", "updated"
- Keep under 50 characters for the subject line
- Start with lowercase (conventional commits are case-insensitive)
- Be specific and descriptive

### Body & Footer

- **Body**: Explain what and why, not how (implementation details go in code comments)
- **Footer**: Reference issues with `Fixes #123`, `Closes #456`, or breaking changes with `BREAKING CHANGE:`

## Breaking Changes

For changes that break backward compatibility:

```
feat(api): remove deprecated filter hooks

BREAKING CHANGE: The following filter hooks are removed:
- `easycommerce_fakerpress_old_filter`
- `easycommerce_fakerpress_legacy_hook`

Use the new hooks documented in the migration guide.
```

## Workflow Integration

### Branch Naming

- `feature/description` for new features
- `fix/issue-description` for bug fixes
- `docs/update-readme` for documentation
- `chore/update-dependencies` for maintenance

### Pull Request Process

1. Create feature branch from `main`
2. Make focused commits following these guidelines
3. Push branch and create pull request
4. Ensure PR description explains the changes
5. Wait for review and CI checks
6. Squash merge with conventional commit message

### Automated Tools

- **Semantic Release**: Automatically determines version bumps
- **Changelog Generation**: Creates release notes from commit messages
- **Linting**: Commit message format validation in CI

## Common Mistakes to Avoid

❌ **Too Vague:**

```
fix: update code
feat: add stuff
```

✅ **Specific:**

```
fix(validation): handle empty product names
feat(customer): add loyalty tier selection
```

❌ **Multiple Changes:**

```
fix: fix bugs and update docs
```

✅ **Separate Commits:**

```
fix(order): resolve status update bug
docs(order): update status workflow documentation
```

❌ **Wrong Type:**

```
feat: fix typo in documentation
```

✅ **Correct Type:**

```
docs(readme): fix typo in installation section
```

## Tools & Validation

### Commit Message Linting

Use tools like `commitlint` to validate message format:

```bash
echo "feat(product): add bulk generation" | commitlint
```

### Interactive Rebase

To fix commit messages:

```bash
git rebase -i HEAD~3
# Change 'pick' to 'reword' for commits to edit
```

### Git Aliases

Add helpful aliases to `.gitconfig`:

```ini
[alias]
ci = commit
cm = commit -m
ca = commit --amend
cane = commit --amend --no-edit
```

## Resources

- [Conventional Commits Specification](https://www.conventionalcommits.org/)
- [Angular Commit Guidelines](https://github.com/angular/angular/blob/main/CONTRIBUTING.md#commit)
- [Semantic Versioning](https://semver.org/)

---

For questions about commit conventions, see AGENTS.md or contact a maintainer.
