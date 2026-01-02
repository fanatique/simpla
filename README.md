# Simpla – A Simple but Robust Static Website Generator

## About

Simpla is a static website generator designed to *just work*, even if you update your site very rarely.

It doesn't have an awful lot of features, but it also has almost no dependencies—no need to perform the `npm/composer/wp-cli update` raindance every time you only wanted to write a quick status update. The engine targets **PHP 8.5** and ships with a tiny built-in Markdown/frontmatter parser, so no external Markdown libraries are required.

> **Fair Warning:** Using Simpla might lead to a situation where you actually have to write that blog post you wanted to write, with no chance to blame the machines for not doing it! 😄

## Features

- **Blog Posts & Static Pages** – Write content in Markdown with YAML frontmatter
- **Automatic Index Generation** – Blog post indexes and tag pages are generated automatically
- **RSS Feed** – Because we ❤️ independent publications!
- **Flexible Menus** – Define main navigation, footer links, and social menus in config
- **Themes** – Full control over templates using PHP (`.phtml`) files
- **Code Highlighting** – Built-in support via highlight.js
- **Docker-Ready** – Ship your site with a single command

## Quick Start

```shell
# 1. Create your project folder
mkdir my-website && cd my-website

# 2. Download the page folder from this repo (or clone the whole repo)
curl -L https://github.com/fanatique/simpla/archive/main.zip -o simpla.zip
unzip simpla.zip "simpla-main/page/*"
mv simpla-main/page ./page && rm -rf simpla-main simpla.zip

# 3. Create a dist folder for the generated output
mkdir dist

# 4. Generate your website
docker run -it --rm \
  -v "$PWD/page":/usr/src/simpla/page \
  -v "$PWD/dist":/usr/src/simpla/dist \
  fanatique/simpla:latest

# 5. Preview locally (optional)
cd dist && php -S localhost:8080
```

Open http://localhost:8080 in your browser and you'll see your generated website!

## Project Structure

```
my-website/
├── page/                      # Your website source
│   ├── config/
│   │   └── config.php         # Site configuration
│   ├── content/
│   │   ├── pages/             # Static pages (about, contact, etc.)
│   │   ├── posts/             # Blog posts
│   │   ├── snippets/          # Reusable content blocks
│   │   └── img/               # Media files
│   └── views/
│       └── [theme]/           # Templates and assets
├── dist/                      # Generated output (auto-created)
└── .github/workflows/         # CI/CD (optional)
```

## Documentation

| Guide | Description |
|-------|-------------|
| [Getting Started](documentation/01.getting-started.md) | Minimal steps to publish a website |
| [Setting Up a Custom Page](documentation/02.setting-up-a-customized-page.md) | Deep dive into templates and theming |
| [Creating Content](documentation/03.creating-content.md) | Writing posts, pages, and working with media |
| [GitHub Actions Deployment](documentation/04.github-actions-deployment.md) | Automated builds and deployment |

## Contributing

I've written Simpla mainly for myself, but I like the idea and had to document it anyway.

If you have ideas on how to improve Simpla:
- Open an issue for discussion
- Submit a pull request (preferred)

Please note: This is a practical tool, not a showcase project. Suggestions focused purely on coding style will be politely declined.

## License

MIT – See [LICENSE](LICENSE) for details.
