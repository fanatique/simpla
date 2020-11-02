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
use Simpla\Content\ContentGenerator;
use Simpla\Content\ContentIndexGenerator;
use Simpla\Content\TagIndexGenerator;
use Simpla\Content\MenuGenerator;
use Simpla\Content\FeedGenerator;
use Simpla\Asset\AssetHandler;
use Pagerange\Markdown\MetaParsedown;

$container = new Container();

// Prepare Application Config
$appConfigJson = file_get_contents(__DIR__ . '/config/app_config.json');
$container->appConfig = json_decode($appConfigJson);

// Prepare Site Config
$siteConfigJson = file_get_contents(__DIR__ . '/../page/config/site_config.json');
$container->siteConfig = json_decode($siteConfigJson);

// Register Handlers, Factories and Generators in the Container

$container->assetHandler = function (): AssetHandler {
    return new AssetHandler();
};

$container->entityFactory = function (): EntityFactory {
    $markdownParser = new MetaParsedown();
    return new EntityFactory($markdownParser);
};

$container->contentIteratorFactory = function () use ($container): ContentIteratorFactory {
    return new ContentIteratorFactory($container('entityFactory'));
};

// TODO Add view folders and content folders to generators

$container->menuGenerator = function () use ($container): MenuGenerator {
    return new MenuGenerator(
        $container('appConfig')->views->menu,
        $container('siteConfig'),
        $container('appConfig')
    );
};

$container->postGenerator = function () use ($container): ContentGenerator {
    $postGenerator = new ContentGenerator(
        $container('appConfig')->views->post,
        $container('siteConfig'),
        $container('appConfig')
    );
    return $postGenerator;
};

$container->pageGenerator = function () use ($container): ContentGenerator {
    $pageGenerator = new ContentGenerator(
        $container('appConfig')->views->page,
        $container('siteConfig'),
        $container('appConfig')
    );
    return $pageGenerator;
};

$container->postIndexGenerator = function () use ($container): ContentIndexGenerator {
    $postIndexGenerator = new ContentIndexGenerator(
        $container('appConfig')->views->index,
        $container('siteConfig')->slugs->post_index,
        $container('siteConfig'),
        $container('appConfig')
    );
    return $postIndexGenerator;
};

$container->tagIndexGenerator = function (array $generatedMenus = []) use ($container): TagIndexGenerator {
    $tagIndexGenerator = new TagIndexGenerator(
        $container('appConfig')->views->tag,
        $container('siteConfig'),
        $container('appConfig')
    );
    return $tagIndexGenerator;
};

$container->feedGenerator = function () use ($container): FeedGenerator {
    $feedGenerator = new FeedGenerator(
        $container('siteConfig')->slugs->feed,
        $container('siteConfig'),
        $container('appConfig')
    );
    return $feedGenerator;
};

return $container;
