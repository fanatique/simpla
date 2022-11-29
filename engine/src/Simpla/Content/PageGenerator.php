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
use Simpla\Content\ContentGenerator;

class PageGenerator extends ContentGenerator implements ContentGeneratorInterface
{
  
    protected $sectionStore;
  
    public function __construct(string $defaultTemplate, object $config, ContentIterator $sectionStore)
    {
        $this->sectionStore = $sectionStore;
      
        parent::__construct($defaultTemplate, $config);
    }

    public function generateOne(EntityInterface $entity, array $generatedMenus = []): string
    {
        ob_start();

        // Make config available to the template
        $config = $this->config;
        $sections = $this->sectionStore;
        
        $template = $entity->get('template') ?? $this->defaultTemplate;

        // Render template (including immediately executes script!)
        include $config->folders->views . $config->theme . \DIRECTORY_SEPARATOR . $template;

        // Write buffer into output array
        $generatedContent = ob_get_contents();

        ob_end_clean();

        return $generatedContent;
    }
}
