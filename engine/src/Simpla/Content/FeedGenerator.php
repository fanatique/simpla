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
use \Suin\RSSWriter\Feed;
use \Suin\RSSWriter\Channel;
use \Suin\RSSWriter\Item;

class FeedGenerator implements ContentGeneratorInterface
{
    use ExtractAndSortEntitiesTrait;

    protected $feed;
    protected $template;
    protected $siteConfig;
    protected $appConfig;
    protected $slug;

    public function __construct(string $slug, object $siteConfig, object $appConfig)
    {
        $this->slug = $slug;
        $this->siteConfig = $siteConfig;
        $this->appConfig = $appConfig;

        $this->feed = new Feed();
        $this->channel = $this->setupChannel();
        $this->channel->appendTo($this->feed);
    }

    public function generate(ContentIterator $posts): array
    {
        $entities = $this->extractAndSortEntities($posts);

        foreach ($entities as $entity) {
            $description = $entity->get('description') ?? $entity->get('content');

            $item = new Item();
            $item
                ->title($entity->get('title'))
                ->description($description)
                ->contentEncoded($entity->get('content'))
                ->url($entity->getSlug($this->siteConfig->base_url))
                ->author($entity->get('author') ?? $this->siteConfig->author)
                ->pubDate($entity->get('created_at')->getTimestamp())
                ->guid($entity->getSlug($this->siteConfig->base_url), true)
                ->preferCdata(true) // By this, title and description become CDATA wrapped HTML.
                ->appendTo($this->channel);
        }

        return [$this->slug => $this->feed->render()];
    }

    protected function setupChannel(): Channel
    {
        $channel = new Channel();
        $channel
            ->title($this->siteConfig->title)
            ->description($this->siteConfig->description)
            ->url($this->siteConfig->base_url)
            ->feedUrl($this->siteConfig->base_url . '/' . $this->slug . '.' . $this->appConfig->file_extension_feed)
            ->language($this->siteConfig->language)
            ->copyright('Copyright ' . date('Y') . ', ' . $this->siteConfig->author)
            ->pubDate(strtotime('now'))
            ->lastBuildDate(strtotime('now'))
            ->ttl(180);

        return $channel;
    }
}
