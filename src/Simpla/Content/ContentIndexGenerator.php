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
    protected $siteConfig;
    protected $appConfig;
    protected $slug;

    public function __construct(string $template, string $slug, object $siteConfig, object $appConfig)
    {
        $this->template = $template;
        $this->siteConfig = $siteConfig;
        $this->appConfig = $appConfig;
        $this->slug = $slug;
    }

    public function generate(ContentIterator $contentItems, array $generatedMenus = []): array
    {
      return [$this->slug => $this->generateOne()];
    }

    public function generateOne(ContentIterator $contentItems, array $generatedMenus = []): string
    {
      ob_start();

      $siteConfig = $this->siteConfig;
      $slug = $this->slug;

      $entities = $this->extractAndSortEntities($contentItems);
      $displayExcerpt = true;

      // Make siteconfig available to the template
      $siteConfig = $this->siteConfig;

      // Make appconfig available to the template
      $appConfig = $this->appConfig;

      // Render template (including immediately executes script!)
      include $appConfig->folders->views . $this->template;

      // Write buffer into output variable
      $generatedContent = ob_get_contents();
      ob_end_clean();

      return $generatedContent;
    }
}
