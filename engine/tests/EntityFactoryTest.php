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
use Simpla\Entity\EntityFactory;
use Simpla\Entity\Post;
use Simpla\Markdown\MarkdownParser;

final class EntityFactoryTest extends TestCase
{
    public function testCreatesPostFromMarkdown(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'post');
        $markdown = <<<MD
---
title: 'Sample'
status: published
created_at: 2024-01-10
tags: alpha, beta
---

Content body.
MD;
        file_put_contents($tmp, $markdown);

        $factory = new EntityFactory(new MarkdownParser());
        $entity = $factory->createFromMarkdown($tmp, Post::TYPE);

        self::assertInstanceOf(Post::class, $entity);
        self::assertSame('Sample', $entity->get('title'));
        self::assertSame('published', $entity->get('status'));
        self::assertSame('2024-01-10', $entity->get('created_at')->format('Y-m-d'));
        self::assertSame(['alpha', 'beta'], $entity->get('tags'));
        self::assertStringContainsString('<p>Content body.</p>', $entity->get('content'));
    }

    public function testThrowsOnMissingFile(): void
    {
        $factory = new EntityFactory(new MarkdownParser());
        $this->expectException(\Simpla\Entity\EntityException::class);
        $factory->createFromMarkdown('/non/existent/file.md', Post::TYPE);
    }
}

