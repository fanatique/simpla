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

use Simpla\Content\ExtractAndSortEntitiesTrait;
use Simpla\Entity\EntityInterface;

class SitemapGenerator implements ContentGeneratorInterface
{
    use ExtractAndSortEntitiesTrait;

    protected string $slug;
    protected object $config;

    public function __construct(string $slug, object $config)
    {
        $this->slug = $slug;
        $this->config = $config;
    }

    /**
     * Builds a sitemap.xml from all published posts, pages, the blog index
     * and every tag archive page.
     *
     * @param array $tags Map of tagName => entities[] (see ContentIterator::sortByEntityTags()).
     * @return array<string, string> [slug => renderedXml] — empty when base_url is not configured.
     */
    public function generate(ContentIterator $posts, ContentIterator $pages, array $tags): array
    {
        $baseUrl = (string) $this->config->base_url;
        if ($baseUrl === '') {
            error_log('Simpla: base_url is empty, skipping sitemap generation.');
            return [];
        }

        $urls = [];

        foreach ($this->extractAndSortEntities($posts) as $entity) {
            $this->appendEntity($urls, $entity, $baseUrl);
        }

        foreach ($this->extractAndSortEntities($pages) as $entity) {
            $this->appendEntity($urls, $entity, $baseUrl);
        }

        // Blog index (e.g. /blog).
        $urls[] = ['loc' => $baseUrl . '/' . $this->config->slugs->post_index];

        // Tag archive pages (e.g. /tags/blogging).
        foreach (array_keys($tags) as $tagName) {
            $urls[] = ['loc' => $baseUrl . '/' . $this->config->tag_path . '/' . $tagName];
        }

        return [$this->slug => $this->renderSitemap($urls)];
    }

    /**
     * Adds an entity to the URL list unless it opted out via `sitemap: false`.
     * The homepage (slug "index") is mapped to the site root.
     */
    private function appendEntity(array &$urls, EntityInterface $entity, string $baseUrl): void
    {
        if ($entity->get('sitemap') === false) {
            return;
        }

        $loc = $entity->getSlug($baseUrl);
        if ($loc === $baseUrl . '/index') {
            $loc = $baseUrl . '/';
        }

        $entry = ['loc' => $loc];

        $createdAt = $entity->get('created_at');
        if ($createdAt instanceof \DateTimeInterface) {
            $entry['lastmod'] = $createdAt->format('Y-m-d');
        }

        $urls[] = $entry;
    }

    private function renderSitemap(array $urls): string
    {
        $writer = new \XMLWriter();
        $writer->openMemory();
        $writer->setIndent(true);
        $writer->startDocument('1.0', 'UTF-8');

        $writer->startElement('urlset');
        $writer->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        foreach ($urls as $url) {
            $writer->startElement('url');
            $writer->writeElement('loc', $url['loc']);
            if (isset($url['lastmod'])) {
                $writer->writeElement('lastmod', $url['lastmod']);
            }
            $writer->endElement();
        }

        $writer->endElement();
        $writer->endDocument();

        return $writer->outputMemory();
    }
}
