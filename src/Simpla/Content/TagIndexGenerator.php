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
    protected $siteConfig;
    protected $appConfig;
    protected $generatedMenus = [];

    public function __construct(string $template, object $siteConfig, object $appConfig)
    {
        $this->template = $template;
        $this->siteConfig = $siteConfig;
        $this->appConfig = $appConfig;
    }

    public function generate(array $tags, array $generatedMenus = []): array
    {
        $generatedTags = [];
        foreach ($tags as $tagName => $entities) {
            $slug = $tagName;
            $generatedTags[$slug] = $this->generateTagIndex($entities, $generatedMenus);
        }
        return $generatedTags;
    }

    public function generateTagIndex(array $entities, array $generatedMenus = []): string
    {
        ob_start();
        // Make siteconfig available to the template
        $siteConfig = $this->siteConfig;

        // Make appconfig available to the template
        $appConfig = $this->appConfig;

        // Render template (including immediately executes script!)
        include $appConfig->folders->views . $this->template;

        // Write buffer into output array
        $generatedEntity = (string) ob_get_contents();

        ob_end_clean();

        return $generatedEntity;
    }
}
