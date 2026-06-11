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

class RobotsGenerator implements ContentGeneratorInterface
{
    protected string $slug;
    protected object $config;

    public function __construct(string $slug, object $config)
    {
        $this->slug = $slug;
        $this->config = $config;
    }

    /**
     * Builds a permissive robots.txt that references the sitemap (when a
     * base_url is configured).
     *
     * @return array<string, string> [slug => robotsTxt]
     */
    public function generate(): array
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
        ];

        $baseUrl = (string) $this->config->base_url;
        if ($baseUrl !== '') {
            $sitemapUrl = $baseUrl . '/' . $this->config->slugs->sitemap
                . '.' . $this->config->file_extension_sitemap;
            $lines[] = '';
            $lines[] = 'Sitemap: ' . $sitemapUrl;
        }

        return [$this->slug => implode("\n", $lines) . "\n"];
    }
}
