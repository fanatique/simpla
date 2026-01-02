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
use Simpla\Content\TagIndexGenerator;
use Simpla\Entity\Post;
use Simpla\Entity\Snippet;

final class TagIndexGeneratorTest extends TestCase
{
    public function testGeneratesTagIndex(): void
    {
        $views = sys_get_temp_dir() . '/simpla-tag-' . uniqid();
        $theme = 't';
        $template = 'tag.phtml';
        $path = $views . '/' . $theme;
        mkdir($path, 0777, true);

        file_put_contents($path . '/' . $template, <<<'PHTML'
<section>
  <h1><?= $slug; ?></h1>
  <p><?= $snippet ? $snippet->get('content') : ''; ?></p>
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

        $snippet = Snippet::createFromArray([
            'title' => 'php',
            'status' => 'published',
            'created_at' => '2024-01-01',
            'content' => 'Snippet body',
        ]);

        $dummyDir = sys_get_temp_dir() . '/simpla-snippet-' . uniqid();
        mkdir($dummyDir);
        file_put_contents($dummyDir . '/dummy.md', "---\n");

        $snippetStore = new class($dummyDir, $snippet) extends \Simpla\Content\ContentIterator {
            private $snippet;
            public function __construct(string $dir, $snippet)
            {
                $dummyFactory = new class extends \Simpla\Entity\EntityFactory {
                    public function __construct() {}
                    public function createFromMarkdown(string $pathToFile, string $type): \Simpla\Entity\EntityInterface
                    {
                        throw new \RuntimeException('unused');
                    }
                };
                parent::__construct($dir, \Simpla\Entity\Post::TYPE, $dummyFactory);
                $this->snippet = $snippet;
            }
            public function findByFieldValue(string $field, string $value): ?\Simpla\Entity\EntityInterface
            {
                return ($field === 'title' && $value === $this->snippet->get('title')) ? $this->snippet : null;
            }
        };

        $post = Post::createFromArray([
            'title' => 'Tagged post',
            'status' => 'published',
            'created_at' => '2024-01-02',
            'content' => '<p>Body</p>',
        ]);

        $generator = new TagIndexGenerator($template, $config, $snippetStore);
        $html = $generator->generateTagIndex('php', [$post], []);

        self::assertStringContainsString('<h1>php</h1>', $html);
        self::assertStringContainsString('Snippet body', $html);
        self::assertStringContainsString('Tagged post', $html);
    }
}

