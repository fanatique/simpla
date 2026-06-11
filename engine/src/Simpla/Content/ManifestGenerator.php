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

class ManifestGenerator implements ContentGeneratorInterface
{
    protected string $slug;
    protected object $config;

    public function __construct(string $slug, object $config)
    {
        $this->slug = $slug;
        $this->config = $config;
    }

    /**
     * Builds a site.webmanifest from the site config. Name and description
     * are derived from the global config; the rest comes from the optional
     * `manifest` config block.
     *
     * @return array<string, string> [slug => json] — empty when no manifest config is present.
     */
    public function generate(): array
    {
        if (!isset($this->config->manifest)) {
            return [];
        }

        $manifest = $this->config->manifest;

        $data = [
            'name' => $this->config->title,
            'short_name' => $manifest->short_name ?? $this->config->title,
            'description' => $this->config->description,
            'lang' => $this->config->language,
            'start_url' => '/',
            'display' => $manifest->display ?? 'standalone',
            'theme_color' => $manifest->theme_color ?? '#ffffff',
            'background_color' => $manifest->background_color ?? '#ffffff',
            'icons' => isset($manifest->icons) ? (array) $manifest->icons : [],
        ];

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return [$this->slug => $json];
    }
}
