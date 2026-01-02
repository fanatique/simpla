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

use Simpla\Container\Container;
use Simpla\Entity\EntityFactory;
use Simpla\Content\ContentIteratorFactory;
use Simpla\Content\ContentIterator;
use Simpla\Content\ContentGenerator;
use Simpla\Content\ContentIndexGenerator;
use Simpla\Content\TagIndexGenerator;
use Simpla\Content\MenuGenerator;
use Simpla\Content\FeedGenerator;
use Simpla\Asset\AssetHandler;
use Simpla\Asset\ImageOptimizer;
use Simpla\Markdown\MarkdownParser;
use Simpla\Entity\Snippet;

$container = new Container();

// Prepare Config
//$configJson = file_get_contents(__DIR__ . '/../page/config/config.json');
//$container->config = json_decode($configJson);
$container->config = require __DIR__ . '/../../page/config/config.php';

// Register Handlers, Factories and Generators in the Container

$container->assetHandler = function (): AssetHandler {
    return new AssetHandler();
};

$container->imageOptimizer = function () use ($container): ImageOptimizer {
    return new ImageOptimizer($container('assetHandler'));
};

$container->entityFactory = function () use ($container): EntityFactory {
    $markdownParser = new MarkdownParser(false, [
        'image_lazy' => $container('config')->images->lazy ?? true,
    ]);
    return new EntityFactory($markdownParser);
};

$container->contentIteratorFactory = function () use ($container): ContentIteratorFactory {
    return new ContentIteratorFactory($container('entityFactory'));
};

$container->menuGenerator = function () use ($container): MenuGenerator {
    return new MenuGenerator(
        $container('config')->views->menus,
        $container('config')
    );
};

$container->snippetStore = function () use ($container): ContentIterator {
    return new ContentIterator(
        $container('config')->folders->content . $container('config')->content->snippets,
        Snippet::TYPE,
        $container('entityFactory')
    );
};


$container->postGenerator = function () use ($container): ContentGenerator {
    $postGenerator = new ContentGenerator(
        $container('config')->views->post,
        $container('config')
    );
    return $postGenerator;
};

$container->pageGenerator = function () use ($container): ContentGenerator {
    $pageGenerator = new ContentGenerator(
        $container('config')->views->page,
        $container('config')
    );
    return $pageGenerator;
};

$container->postIndexGenerator = function () use ($container): ContentIndexGenerator {
    $postIndexGenerator = new ContentIndexGenerator(
        $container('config')->views->post_index,
        $container('config')->slugs->post_index,
        $container('config')
    );
    return $postIndexGenerator;
};

$container->tagIndexGenerator = function (array $generatedMenus = []) use ($container): TagIndexGenerator {
    $tagIndexGenerator = new TagIndexGenerator(
        $container('config')->views->tag,
        $container('config'),
        $container('snippetStore')
    );
    return $tagIndexGenerator;
};

$container->feedGenerator = function () use ($container): FeedGenerator {
    $feedGenerator = new FeedGenerator(
        $container('config')->slugs->feed,
        $container('config')
    );
    return $feedGenerator;
};

return $container;
