# Improvements & Roadmap

This document tracks completed improvements, planned features, and useful commands for development.

---

## ✅ Completed

### Engine Modernization
- **PHP 8.5** target platform with Composer v2 in Dockerfile
- `composer.json` / `composer.lock` cleaned up and updated

### Dependency Reduction
- Removed `parsedown` / `metaparsedown` dependencies
- Implemented custom `Simpla\Markdown\MarkdownParser` with built-in frontmatter support

### Error Handling & Robustness
- **EntityFactory**: File existence checks, required field validation
- **ContentIterator**: Path validation, `.md` filter, hidden file exclusion
- **AssetHandler**: Clear error messages, safe copy operations
- Fixed typed properties and dynamic property issues across codebase

### Template System
- Encapsulated rendering via `TemplateRendererTrait`
- Generators now use scoped includes with proper error handling for missing templates

### Testing
- Added PHPUnit test suite (`phpunit.xml.dist`)
- Test coverage for:
  - `MarkdownParserTest`
  - `EntityFactoryTest`
  - `ContentIteratorTest`
  - `ContentGeneratorTest`
  - `ContentIndexGeneratorTest`
  - `TagIndexGeneratorTest`
- Added `composer test` script

---

## 🚧 Planned / Ideas

### Additional Tests
- [ ] **FeedGenerator** – RSS snapshot testing with stabilized timestamps
- [ ] **MenuGenerator** – Internal/external link generation tests
- [ ] **AssetHandler** – Copy/write failure path testing

### Parser Enhancements
- [x] Table support in Markdown
- [x] Stricter frontmatter parsing (YAML-compatible)
- [x] Optional escaping strategy documentation
- [x] Code block language detection improvements

### Build & CI
- [x] ~~CI pipeline setup~~ – See [GitHub Actions guide](documentation/04.github-actions-deployment.md)
- [ ] Automated Docker image builds on release
- [ ] Version tagging for Docker images

### Documentation
- [x] ~~Comprehensive documentation rewrite~~ – Complete!
- [x] ~~API reference for template variables~~ – See [Template API Reference](documentation/05.template-api-reference.md)
- [ ] Video tutorials

### Features (Maybe)
- [ ] Sitemap generation
- [ ] JSON feed support (alongside RSS)
- [ ] Draft preview mode (build drafts to separate folder)
- [ ] Incremental builds (only changed content)
- [ ] Image optimization during build

---

## Development Commands

### Local Development (from `engine/` directory)

```bash
# Install dependencies (including dev)
composer install

# Build the website (writes to ../dist)
composer build

# Run tests
composer test

# Run specific test
./vendor/bin/phpunit tests/MarkdownParserTest.php
```

### Docker Build (from project root)

```bash
# Create dist folder
mkdir -p dist

# Build using Docker
docker run --rm \
  -v "$PWD/page":/usr/src/simpla/page \
  -v "$PWD/dist":/usr/src/simpla/dist \
  fanatique/simpla:latest

# Or with explicit command
docker run --rm \
  -v "$PWD/page":/usr/src/simpla/page \
  -v "$PWD/dist":/usr/src/simpla/dist \
  -w /usr/src/simpla/engine \
  fanatique/simpla:latest \
  composer build
```

### Local Preview Server

```bash
# From dist folder
cd dist
php -S localhost:8080

# Or with Python
python -m http.server 8080
```

### Building the Docker Image

```bash
# From project root
docker build -t fanatique/simpla:latest .

# Test the image
docker run --rm fanatique/simpla:latest composer --version
```

---

## Code Style Notes

- License headers should be added to new PHP files (MIT license)
- Use strict types: `declare(strict_types=1);`
- Follow PSR-4 autoloading conventions
- Keep dependencies minimal – that's a core design principle!
