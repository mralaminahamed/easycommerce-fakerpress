# Copilot Instructions for easycommerce-fakerpress Plugin

## Purpose
This file provides guidelines for using GitHub Copilot in the easycommerce-fakerpress plugin project.

## Coding Standards
- Follow WordPress PHP coding standards for all PHP files.
- Use ES6+ syntax for JavaScript/React code in the `src/` directory.
- Use Tailwind CSS for styling in frontend components.
- Ensure code is linted and formatted before committing.

## Best Practices
- Write clear, self-documenting code and add comments where necessary.
- Prefer functional components and hooks in React.
- Use dependency injection and avoid global state in PHP classes.
- Keep functions and components small and focused.
- Avoid duplicating code; use shared utilities/components.

## File Structure
- `includes/`: PHP generators for coupons, customers, orders, products.
- `src/admin/components/`: React components for admin UI.
- `build/`: Compiled assets, do not edit directly.

## Commit Guidelines
- Follow Conventional Commits (see git-commit-instructions.md).
- Reference related issues in commit messages when applicable.

## Testing
- Add or update tests for new features and bug fixes.
- Use PHPUnit for PHP tests and Jest/React Testing Library for JS/React tests (if configured).

## Documentation
- Update README.md for major changes or new features.
- Document public APIs and important functions/classes.

## Copilot Usage
- Use Copilot to suggest code, but always review and test before committing.
- Do not accept Copilot suggestions that violate project standards or introduce security risks.
- Refactor Copilot-generated code to match project conventions if needed.

---
For questions, contact a maintainer or refer to project documentation.

