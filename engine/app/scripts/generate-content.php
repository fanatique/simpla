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

require_once __DIR__ . '/../../vendor/autoload.php';
$container = require_once __DIR__ . '/../bootstrap_container.php';

$saveContentCallback = function ($content, $slug) use ($container) {
    $container('assetHandler')->persistContent(
        $content,
        $container('config')->folders->dist,
        $slug,
        $container('config')->file_extension_content
    );
};

$posts = $container('contentIteratorFactory')->create(
    $container('config')->folders->content . $container('config')->content->posts,
    Simpla\Entity\Post::TYPE
);

$pages = $container('contentIteratorFactory')->create(
    $container('config')->folders->content . $container('config')->content->pages,
    Simpla\Entity\Page::TYPE
);
$tags = $posts->sortByEntityTags();

$generatedMenus = $container('menuGenerator')->generate($container('config')->menus);

$generatedPosts = $container('postGenerator')->generate($posts, $generatedMenus);
array_walk($generatedPosts, $saveContentCallback);

$generatedPages = $container('pageGenerator')->generate($pages, $generatedMenus);
array_walk($generatedPages, $saveContentCallback);

$generatedIndex = $container('postIndexGenerator')->generate($posts, $generatedMenus);
array_walk($generatedIndex, $saveContentCallback);

$generatedTags = $container('tagIndexGenerator')->generate($tags, $generatedMenus);
array_walk($generatedTags, function ($content, $slug) use ($container) {
    $container('assetHandler')->persistContent(
        $content,
        $container('config')->folders->dist_tags,
        $slug,
        $container('config')->file_extension_content
    );
});

$generatedFeed = $container('feedGenerator')->generate($posts);
array_walk($generatedFeed, function ($content, $slug) use ($container) {
    $container('assetHandler')->persistContent(
        $content,
        $container('config')->folders->dist,
        $slug,
        $container('config')->file_extension_feed
    );
});
