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

class MenuGenerator implements ContentGeneratorInterface
{
    protected $templates;
    protected $siteConfig;
    protected $appConfig;

    public function __construct(array $templates, object $siteConfig, object $appConfig)
    {
        $this->templates = $templates;
        $this->siteConfig = $siteConfig;
        $this->appConfig = $appConfig;
    }

    private function extractEntities(ContentIterator $contentItems): array
    {
        $entities = [];
        foreach ($contentItems as $content) {
            /** @var Simpla\Entity\EntityInterface $content  */
            $entities[] = $content->getEntity();
        }

        return $entities;
    }

    public function generate(ContentIterator $contentItems): array
    {
        $entities = $this->extractEntities($contentItems);
        $generatedMenus = [];
        foreach ($this->templates as $template) {
            $menuName = $this->extractFilenameFromPath($template);
            $generatedMenus[$menuName] = $this->renderMenu($template, $entities);
        }

        return $generatedMenus;
    }

    private function extractFilenameFromPath(string $templatePath): string
    {
        $info = pathinfo($templatePath);
        $fileName =  basename($templatePath, '.' . $info['extension']);

        return $fileName;
    }

    protected function renderMenu(string $template, array $entities): string
    {
        ob_start();
        $siteConfig = $this->siteConfig;
        $appConfig = $this->appConfig;
        include $template;
        $generatedEntity = ob_get_contents();
        ob_end_clean();

        return (string) $generatedEntity;
    }
}
