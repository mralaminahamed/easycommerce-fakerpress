# Git Commit Instructions for easycommerce-fakerpress Plugin

## Commit Message Format

Please use [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/) for all commit messages. This helps maintain clarity and consistency in the project history.

**Format:**
```
type(scope): short description

[optional body]
[optional footer]
```

### Types
- feat: A new feature for the plugin
- fix: A bug fix
- docs: Documentation only changes
- style: Changes that do not affect the meaning of the code (white-space, formatting, etc)
- refactor: Code change that neither fixes a bug nor adds a feature
- perf: Performance improvement
- test: Adding or correcting tests
- chore: Maintenance tasks (build, dependencies, etc)

### Scope
Use the relevant area of the plugin, e.g. `product`, `order`, `customer`, `admin`, etc.

**Examples:**
- feat(product): add bulk product generation
- fix(order): correct order status assignment
- docs: update README with usage instructions

## Best Practices
- Make small, focused commits.
- Reference related issues in the footer (e.g. `Closes #123`).
- Test your changes before committing.
- Do not commit build files unless necessary.

## Workflow
1. Pull the latest changes before starting work.
2. Create a new branch for your feature or fix.
3. Make your changes and commit using the format above.
4. Push your branch and open a pull request.

---
For questions, see README.md or contact a maintainer.

