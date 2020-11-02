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

class ContentGenerator implements ContentGeneratorInterface
{
    protected $defaultTemplate;
    protected $siteConfig;
    protected $appConfig;

    public function __construct(string $defaultTemplate, object $siteConfig, object $appConfig)
    {
        $this->defaultTemplate = $defaultTemplate;
        $this->siteConfig = $siteConfig;
        $this->appConfig = $appConfig;
    }

    public function generate(ContentIterator $contentItems, array $generatedMenus = []): array
    {
        $generatedEntities = [];
        foreach ($contentItems as $contentItem) {
            ob_start();
            /** @var ContentIterator $contentItem */
            $entity = $contentItem->getEntity();
            
            if ($entity->get('status') !== 'published') {
                continue;
            }
            // Make siteconfig available to the template
            $siteConfig = $this->siteConfig;

            // Make appconfig available to the template
            $appConfig = $this->appConfig;
            
            // Render template (including immediately executes script!)
            include $entity->get('template') ?? $this->defaultTemplate;
            
            // Write buffer into output array
            $generatedEntities[$entity->getSlug()] = ob_get_contents();
            ob_end_clean();
        }

        return $generatedEntities;
    }
}
