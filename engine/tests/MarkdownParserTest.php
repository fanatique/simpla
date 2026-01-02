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
        self::assertStringContainsString('<h1>Heading</h1>', $html);
        self::assertStringContainsString('<strong>bold</strong>', $html);
        self::assertStringContainsString('<code>code</code>', $html);
    }

    public function testUnclosedFrontmatterReturnsEmptyMeta(): void
    {
        $markdown = "---\ntitle: Test\n";
        $parser = new MarkdownParser();

        $meta = $parser->meta($markdown);
        self::assertSame([], $meta);
    }
}

