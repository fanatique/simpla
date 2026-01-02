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

class TagIndexGenerator implements ContentGeneratorInterface
{
    use TemplateRendererTrait;

    protected $template;
    protected $config;
    protected $snippetStore;
    protected $generatedMenus = [];

    public function __construct(string $template, object $config, ContentIterator $snippetStore)
    {
        $this->template = $template;
        $this->config = $config;
        $this->snippetStore = $snippetStore;
    }

    public function generate(array $tags, array $generatedMenus = []): array
    {
        $generatedTags = [];
        foreach ($tags as $tagName => $entities) {
            $generatedTags[$tagName] = $this->generateTagIndex($tagName, $entities, $generatedMenus);
        }
        return $generatedTags;
    }

    public function generateTagIndex(string $tagName, array $entities, array $generatedMenus = []): string
    {
        $config = $this->config;
        $slug = $tagName;
        $snippet = $this->snippetStore->findByFieldValue('title', $tagName);

        $templatePath = $config->folders->views . $this->config->theme . \DIRECTORY_SEPARATOR . $this->template;

        return $this->renderTemplate($templatePath, [
            'config' => $config,
            'slug' => $slug,
            'entities' => $entities,
            'snippet' => $snippet,
            'generatedMenus' => $generatedMenus,
        ]);
    }
}
