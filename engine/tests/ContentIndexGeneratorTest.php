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
use Simpla\Content\ContentIndexGenerator;
use Simpla\Content\ContentIterator;
use Simpla\Entity\EntityFactory;
use Simpla\Entity\Post;
use Simpla\Markdown\MarkdownParser;

final class ContentIndexGeneratorTest extends TestCase
{
    public function testGeneratesIndexFromContentIterator(): void
    {
        $views = sys_get_temp_dir() . '/simpla-idx-' . uniqid();
        $theme = 't';
        $template = 'index.phtml';
        $path = $views . '/' . $theme;
        mkdir($path, 0777, true);

        file_put_contents($path . '/' . $template, <<<'PHTML'
<section>
  <h1>Index</h1>
  <ul>
  <?php foreach ($entities as $e): ?>
    <li><?= $e->get('title'); ?></li>
  <?php endforeach; ?>
  </ul>
</section>
PHTML);

        $config = (object) [
            'folders' => (object) ['views' => $views . '/'],
            'theme' => $theme,
        ];

        $dir = sys_get_temp_dir() . '/simpla-md-idx-' . uniqid();
        mkdir($dir);
        $md = <<<MD
---
title: 'Alpha'
status: published
created_at: 2024-01-02
---

Body
MD;
        file_put_contents($dir . '/alpha.md', $md);

        $iterator = new ContentIterator($dir, Post::TYPE, new EntityFactory(new MarkdownParser()));

        $generator = new ContentIndexGenerator($template, 'index', $config);
        $level = ob_get_level();
        $html = $generator->generateOne($iterator, []);
        while (ob_get_level() > $level) {
            ob_end_clean();
        }
        $this->assertSame($level, ob_get_level());

        self::assertStringContainsString('<h1>Index</h1>', $html);
        self::assertStringContainsString('<li>Alpha</li>', $html);
    }
}

