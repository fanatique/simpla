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

class TagIndexGenerator implements ContentGeneratorInterface
{
    protected $template;
    protected $config;
    protected $generatedMenus = [];

    public function __construct(string $template, object $config)
    {
        $this->template = $template;
        $this->config = $config;
    }

    public function generate(array $tags, array $generatedMenus = []): array
    {
        $generatedTags = [];
        foreach ($tags as $tagName => $entities) {
            $generatedTags[$tagName] = $this->generateTagIndex($tagName, $entities, $generatedMenus);
        }
        return $generatedTags;
    }

    public function generateTagIndex(string $tagName, array $entities, array $generatedMenus = []): string
    {
        ob_start();
        // Make config available to the template
        $config = $this->config;

        $slug = $tagName;

        // Render template (including immediately executes script!)
        include $config->folders->views . $this->config->theme . \DIRECTORY_SEPARATOR . $this->template;

        // Write buffer into output array
        $generatedEntity = (string) ob_get_contents();

        ob_end_clean();

        return $generatedEntity;
    }
}
