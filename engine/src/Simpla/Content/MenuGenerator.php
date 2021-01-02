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

    public function __construct(object $templates, object $siteConfig, object $appConfig)
    {
        $this->templates = $templates;
        $this->siteConfig = $siteConfig;
        $this->appConfig = $appConfig;
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
        $appConfig = $this->appConfig;
        $instance = $this;
        $buildMenuLink = function (object $menuItem) use($instance): string
        {
          return $instance->buildMenuLink($menuItem);
        };
        ob_start();
        $siteConfig = $this->siteConfig;
        include $appConfig->folders->views .  $appConfig->views->menus->{$menuName};
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
        $this->siteConfig->base_url,
        '/',
        $menuItem->internal,
        '.',
        ($menuItem->type === 'feed') ?  $this->appConfig->file_extension_feed :  $this->appConfig->file_extension_content
      ]);
    }
}
