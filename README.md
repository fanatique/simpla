# Simpla – A Simple but Robust Static Website Generator

## About

Simpla is a static website generator that is intended to simply work,
even if you are updating your site very rarely.

It doesn't have an awful lot of features, but it also has almost no
dependencies, so that there is no need to perform the
`npm/composer/wp-cli update` raindance everytime you only wanted to
write a quick status update. The engine now targets PHP 8.5 and ships
with a tiny built-in Markdown/frontmatter parser, so no external
Markdown libraries are required.

__But be aware!__ – Using Simpla might lead to a situation where you actually
have to write that blog post you wanted to write with no chance to blame
the machines for not doing it! 😄

## Features

Simpla supports:

- Writing Blog Posts and Static Pages using Markdown.
- It generates index pages for blog posts and tags and you can decide
  whether the start page should be a Page or an index with your latest
  Posts
- It also generates an RSS Feed (because we ❤️  independent publications)!
- Last but not least you can define menus, which are declared in the templates. That
  allows to have a main menu and a footer menu.

_Everything else is up to you!_

Simpla uses the world famous _Personal Home Page Tools_ (PHP) as a
templating language and so there is virtually no limit on what you can
do for improving SEO, deliver your pages with a good accessibility and
making sure that they are properly displayed on mobile devices.

## Intended Usage

The generator is designed to be used through the public Docker image.

1. Download the `/page` folder from this repo and adjust it to your needs (there's more
   information available in the [Getting Started
Guide](documentation/01.getting-started.md)).
2. Generate your website by running:

```shell
docker run -it -v /path/local/page:/usr/src/simpla/page -v /path/local/dist:/usr/src/simpla/dist --rm fanatique/simpla:latest
```

Done.

## Further Reading


- [Getting Started Guide](documentation/01.getting-started.md)
- [Setting up a Page](documentation/02.setting-up-a-page.md)
- [Creating Content](documentation/03.creating-content.md)
- [Extending Simpla](documentation/04.extending-simpla.md)

## Participation

I've written Simpla mainly for myself (it actually didn't even have a
name at first), but I kinda like the idea and had to document the thing
anyway in order to remember what I've done here...

If you have ideas on how to improve Simpla, feel welcome to suggest it
in the Issues section or (preferred) hand in a PR.

Please note that this is not a showcase project, but a tool that serves a
specific purpose. Suggestions on coding styles and beauty will be friendly ignored, ok?

