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
    protected $sconfig;

    public function __construct(object $templates, object $config)
    {
        $this->templates = $templates;
        $this->config = $config;
    }

    public function generate(object $menus): array
    {
        $generatedMenus = [];
        foreach ($menus as $menuName => $menuItems) {
            $generatedMenus[$menuName] = $this->renderMenu($menuName, $menuItems);
        }

        return $generatedMenus;
    }

    protected function renderMenu(string $menuName, array $menuItems): string
    {
        $config = $this->config;
        $instance = $this;
        $buildMenuLink = function (object $menuItem) use($instance): string
        {
          return $instance->buildMenuLink($menuItem);
        };

        ob_start();

        include $config->folders->views . $config->theme . \DIRECTORY_SEPARATOR . $config->views->menus->{$menuName};

        $generatedEntity = ob_get_contents();

        ob_end_clean();

        return (string) $generatedEntity;
    }

    private function buildMenuLink(object $menuItem): string
    {
      $href = $menuItem->external ?? $this->handleInternalLink($menuItem);
      $label = $menuItem->label;
      return "<a href=\"$href\">$label</a>";
    }

    private function handleInternalLink(object $menuItem): string
    {
      return implode('', [
        $this->config->base_url,
        '/',
        $menuItem->internal,
        '.',
        (isset($menuItem->type) && $menuItem->type === 'feed') ?  $this->config->file_extension_feed :  $this->config->file_extension_content
      ]);
    }
}
