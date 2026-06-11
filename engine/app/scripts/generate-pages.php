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

$pages = $container('contentIteratorFactory')->create(
    $container('config')->folders->content . $container('config')->content->pages,
    Simpla\Entity\Page::TYPE
);

$generatedMenus = $container('menuGenerator')->generate($container('config')->menus);

$generatedPages = $container('pageGenerator')->generate($pages, $generatedMenus);
array_walk($generatedPages, function ($content, $slug) use ($container) {
    $container('assetHandler')->persistContent(
        $content,
        $container('config')->folders->dist,
        $slug,
        $container('config')->file_extension_content
    );
});
