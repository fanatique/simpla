<?php

/*
 * This file is part of fanatique/Simpla.
 *
 * (c) Alexander Thomas <me@alexander-thomas.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Simpla\Markdown\MarkdownParser;

final class MarkdownParserTest extends TestCase
{
    public function testParsesFrontmatterAndBody(): void
    {
        $markdown = <<<MD
---
title: 'Hello'
status: published
created_at: 2024-01-01
---

# Heading

Text with **bold** and `code`.
MD;

        $parser = new MarkdownParser();

        $meta = $parser->meta($markdown);
        self::assertSame('Hello', $meta['title']);
        self::assertSame('published', $meta['status']);
        self::assertSame('2024-01-01', $meta['created_at']);

        $html = $parser->text($markdown);
        self::assertStringContainsString('<h1 id="heading">Heading</h1>', $html);
        self::assertStringContainsString('<strong>bold</strong>', $html);
        self::assertStringContainsString('<code>code</code>', $html);
    }

    public function testHeadingsLevelOneToThreeReceiveIds(): void
    {
        $markdown = <<<MD
# Hello World
## Überschrift ÄÖÜß
### With **Bold** Text!
#### No id here
MD;

        $parser = new MarkdownParser();
        $html = $parser->text($markdown);

        self::assertStringContainsString('<h1 id="hello-world">Hello World</h1>', $html);
        self::assertStringContainsString('<h2 id="überschrift-äöüß">Überschrift ÄÖÜß</h2>', $html);
        self::assertStringContainsString('<h3 id="with-bold-text">With <strong>Bold</strong> Text!</h3>', $html);
        self::assertStringNotContainsString('<h4 id="', $html);
    }

    public function testUnclosedFrontmatterReturnsEmptyMeta(): void
    {
        $markdown = "---\ntitle: Test\n";
        $parser = new MarkdownParser();

        $meta = $parser->meta($markdown);
        self::assertSame([], $meta);
    }

    public function testTypedFrontmatterAndLists(): void
    {
        $markdown = <<<MD
---
title: "Hello: World"
published: true
views: 42
rating: 4.5
tags:
  - php
  - static-sites
inline_tags: [docs, parser]
nullable: null
---
Body
MD;

        $parser = new MarkdownParser();
        $meta = $parser->meta($markdown);

        self::assertSame('Hello: World', $meta['title']);
        self::assertTrue($meta['published']);
        self::assertSame(42, $meta['views']);
        self::assertSame(4.5, $meta['rating']);
        self::assertSame(['php', 'static-sites'], $meta['tags']);
        self::assertSame(['docs', 'parser'], $meta['inline_tags']);
        self::assertNull($meta['nullable']);
    }

    public function testTableRendering(): void
    {
        $markdown = <<<MD
| Language | Creator |
|:---------|:-------:|
| PHP | Rasmus Lerdorf |
| JavaScript | Brendan Eich |
MD;

        $parser = new MarkdownParser();
        $html = $parser->text($markdown);

        self::assertStringContainsString('<table>', $html);
        self::assertStringContainsString('<th>Language</th>', $html);
        self::assertStringContainsString('<th style="text-align:center">Creator</th>', $html);
        self::assertStringContainsString('<td>PHP</td>', $html);
        self::assertStringContainsString('<td style="text-align:center">Brendan Eich</td>', $html);
    }

    public function testCodeBlockLanguageClassIsRendered(): void
    {
        $markdown = <<<MD
```javascript
console.log('hello');
```
MD;
        $parser = new MarkdownParser();

        $html = $parser->text($markdown);

        self::assertStringContainsString('<code class="language-javascript">', $html);
    }

    public function testHtmlEscapingCanBeEnabled(): void
    {
        $markdown = '<script>alert("x")</script>';
        $parser = new MarkdownParser(true);

        $html = $parser->text($markdown);

        self::assertStringNotContainsString('<script>', $html);
        self::assertStringContainsString('&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;', $html);
    }
}

