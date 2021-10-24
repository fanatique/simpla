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
use Simpla\Entity\EntityInterface;

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

          /** @var ContentIterator $contentItem */
          $entity = $contentItem->getEntity();

          if ($entity->get('status') !== 'published') {
            continue;
          }

          $generatedEntities[$entity->getSlug()] = $this->generateOne($entity, $generatedMenus);

        }
        return $generatedEntities;
    }

    public function generateOne(EntityInterface $entity, array $generatedMenus = []): string
    {
        ob_start();

        // Make siteconfig available to the template
        $siteConfig = $this->siteConfig;

        // Make appconfig available to the template
        $appConfig = $this->appConfig;

        $template = $entity->get('template') ?? $this->defaultTemplate;

        // Render template (including immediately executes script!)
        include $appConfig->folders->views . $template;

        // Write buffer into output array
        $generatedContent = ob_get_contents();

        ob_end_clean();

        return $generatedContent;
    }
}
