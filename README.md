# Simpla – A Simple but Robust Static Website Generator

## About

Simpla is a static website generator that is intended to simply work,
even if you are updating your site very rarely.

It doesn't have an awful lot of features, but it also has almost no
dependencies, so that there is no need to perform the
`npm/composer/wp-cli update` raindance everytime you only wanted to
write a quick status update.

__But be aware!__ – Using Simpla might lead to a situation where you actually
have to write that blog post you wanted to write with no chance to blame
the machines for not doing it! 😄

## Features

Simpla supports:

- Writing Blog Posts and Static Pages using Markdown.
- It generates index pages for blog posts and tags.
- It also generates an RSS Feed (because we ❤️ independent publications)!
- Pages can be added to menus, which are declared in the templates. That
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
Guide](documentation/01.getting-started.md)
2. Pull the generator image from Dockerhub: `sudo docker pull fanatique/simpla:latest`
3. Generate your website by running:

```shell
$ sudo docker run -it -v /path/to/your/local/page:/usr/src/simpla/page --rm fanatique/simpla:latest
```

Done.

## Further Reading


- [Getting Started Guide](documentation/01.getting-started.md)
- [Setting up a page](documentation/02.setting-up-a-page.md)
- [Creating Content](documentation/03.creating-content.md)
- [Extending Simpla](documentation/04.extending-simpla.md)

