# Contributing to Titan 'O'

First off, thanks for taking the time to contribute! 🎉

## Table of Contents
- [Code of Conduct](#code-of-conduct)
- [Quick Start](#quick-start)
- [How Can I Contribute?](#how-can-i-contribute)
- [Development Process](#development-process)
- [Pull Request Process](#pull-request-process)
- [Styleguides](#styleguides)

## Code of Conduct

This project and everyone participating in it is governed by our Code of Conduct. By participating, you are expected to uphold this code.

## Quick Start

1. Fork the repository
2. Clone your fork:
```bash
git clone https://github.com/your-username/Titan-O-
cd Titan-O-
```
3. Install dependencies:
```bash
composer install
cp .env.example .env
php artisan key:generate
```
4. Create a branch:
```bash
git checkout -b my-feature
```

## How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check the issue list as you might find out that you don't need to create one. When you are creating a bug report, please include as many details as possible:

* Use a clear and descriptive title
* Describe the exact steps to reproduce the problem
* Provide specific examples to demonstrate the steps
* Describe the behavior you observed
* Explain which behavior you expected to see instead
* Include screenshots if possible

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. When creating an enhancement suggestion, please include:

* A clear and descriptive title
* A detailed description of the proposed functionality
* Explain why this enhancement would be useful
* List any alternatives you've considered
* Include mockups if possible

### Your First Code Contribution

Unsure where to begin? Look for these tags in issues:
* `good first issue` - Issues suitable for newcomers
* `help wanted` - Issues needing extra attention
* `beginner friendly` - Easy entry points into the project

## Development Process

1. Select an issue to work on
2. Create a feature branch
3. Write your code
4. Add tests if applicable
5. Update documentation
6. Submit pull request

### Local Development

```bash
# Start local server
php artisan serve

# Run tests
php artisan test

# Check code style
./vendor/bin/phpcs
```

## Pull Request Process

1. Update the README.md with details of changes if needed
2. Update the documentation if you introduce new features
3. Follow the code style guidelines
4. Write meaningful commit messages
5. Include relevant issue numbers in your PR description

### PR Title Format
```
feat: Add new feature
fix: Fix bug
docs: Update documentation
style: Format code
refactor: Refactor code
test: Add tests
```

## Styleguides

### Git Commit Messages
* Use the present tense ("Add feature" not "Added feature")
* Use the imperative mood ("Move cursor to..." not "Moves cursor to...")
* Limit the first line to 72 characters
* Reference issues and pull requests liberally after the first line

### PHP Styleguide
* Follow PSR-12 coding standards
* Use meaningful variable names
* Add comments for complex logic
* Keep functions small and focused

### Documentation Styleguide
* Use Markdown formatting
* Include code examples when relevant
* Keep language clear and concise
* Update the wiki when needed

## Additional Notes

### Issue Labels
* `bug` - Something isn't working
* `enhancement` - New feature or request
* `documentation` - Improvements or additions to documentation
* `help wanted` - Extra attention is needed
* `good first issue` - Good for newcomers

## Questions?

Feel free to open an issue if you have any questions or need clarification about the contribution process.

Thank you for contributing to Titan 'O'! 🚀
