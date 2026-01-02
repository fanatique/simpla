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
use Simpla\Content\ContentGenerator;
use Simpla\Entity\Post;

final class ContentGeneratorTest extends TestCase
{
    public function testRendersTemplateWithEntity(): void
    {
        $views = sys_get_temp_dir() . '/simpla-tpl-' . uniqid();
        $theme = 't';
        $template = 'post.phtml';
        $path = $views . '/' . $theme;
        mkdir($path, 0777, true);

        file_put_contents($path . '/' . $template, <<<'PHTML'
<article>
  <h1><?= $entity->get('title'); ?></h1>
  <div><?= $entity->get('content'); ?></div>
</article>
PHTML);

        $config = (object) [
            'folders' => (object) ['views' => $views . '/'],
            'theme' => $theme,
        ];

        $entity = Post::createFromArray([
            'title' => 'Test Post',
            'status' => 'published',
            'created_at' => '2024-01-01',
            'content' => '<p>Body</p>',
        ]);

        $generator = new ContentGenerator($template, $config);
        $level = ob_get_level();
        $html = $generator->generateOne($entity, []);
        while (ob_get_level() > $level) {
            ob_end_clean();
        }
        $this->assertSame($level, ob_get_level());

        self::assertStringContainsString('<h1>Test Post</h1>', $html);
        self::assertStringContainsString('<p>Body</p>', $html);
    }
}

