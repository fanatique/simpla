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
use Simpla\Content\ContentIterator;
use Simpla\Entity\EntityFactory;
use Simpla\Entity\Post;
use Simpla\Markdown\MarkdownParser;

final class ContentIteratorTest extends TestCase
{
    public function testIteratesOnlyMarkdownFiles(): void
    {
        $dir = sys_get_temp_dir() . '/simpla-md-' . uniqid();
        mkdir($dir);
        file_put_contents($dir . '/keep.md', $this->sampleMarkdown());
        file_put_contents($dir . '/ignore.txt', 'text');
        file_put_contents($dir . '/.hidden.md', $this->sampleMarkdown());

        $iterator = new ContentIterator($dir, Post::TYPE, new EntityFactory(new MarkdownParser()));

        $files = [];
        foreach ($iterator as $item) {
            $files[] = $item->getFilename();
        }

        self::assertSame(['keep.md'], $files);
    }

    private function sampleMarkdown(): string
    {
        return <<<MD
---
title: 'Sample'
status: published
created_at: 2024-01-10
tags: alpha
---

Content body.
MD;
    }
}

