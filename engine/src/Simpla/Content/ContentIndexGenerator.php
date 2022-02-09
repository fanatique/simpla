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

namespace Simpla\Content;

use Simpla\Content\ContentIterator;
use Simpla\Content\ExtractAndSortEntitiesTrait;

class ContentIndexGenerator implements ContentGeneratorInterface
{
    use ExtractAndSortEntitiesTrait;

    protected $template;
    protected $config;
    protected $slug;

    public function __construct(string $template, string $slug, object $config)
    {
        $this->template = $template;
        $this->config = $config;
        $this->slug = $slug;
    }

    public function generate(ContentIterator $contentItems, array $generatedMenus = []): array
    {
        return [$this->slug => $this->generateOne($contentItems, $generatedMenus)];
    }

    public function generateOne(ContentIterator $contentItems, array $generatedMenus = []): string
    {
        ob_start();

        $config = $this->config;
        $slug = $this->slug;

        $entities = $this->extractAndSortEntities($contentItems);
        $displayExcerpt = true;

        // Render template (including immediately executes script!)
        include $config->folders->views . $config->theme . \DIRECTORY_SEPARATOR . $this->template;

        // Write buffer into output variable
        $generatedContent = ob_get_contents();
        ob_end_clean();

        return $generatedContent;
    }
}
