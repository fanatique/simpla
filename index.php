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

require_once __DIR__ . '/vendor/autoload.php';
$container = require_once __DIR__ . '/app/bootstrap.php';


$contentType = $_GET['type'] ?? 'page';

$posts = $container('contentIteratorFactory')->create(
    $container('appConfig')->folders->content . $container('appConfig')->content->posts,
    Simpla\Entity\Post::TYPE
);


$pages = $container('contentIteratorFactory')->create(
    $container('appConfig')->folders->content . $container('appConfig')->content->pages,
    Simpla\Entity\Page::TYPE
);


$generatedMenus = $container('menuGenerator')->generate($container('siteConfig')->menus);

switch($contentType) {
  case 'page':
    $page = $pages->current()->getEntity();
    $output = $container('pageGenerator')->generateOne($page, $generatedMenus);
    break;
  case 'post':
    $post = $posts->current()->getEntity();
    $output = $container('postGenerator')->generateOne($post, $generatedMenus);
    break;
  case 'tagIndex':
    $tags = $posts->sortByEntityTags();
    $tag = current($tags);
    $output = $container('tagIndexGenerator')->generateTagIndex($tag, $generatedMenus);
    break;
  case 'postIndex':
    $output = $container('postIndexGenerator')->generateOne($posts, $generatedMenus);
    break;
}

echo $output;

