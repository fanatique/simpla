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
$container = require_once __DIR__ . '/../bootstrap.php';

$container('assetHandler')->removeAndCreateFolders([
    $container('appConfig')->folders->dist,
    $container('appConfig')->folders->dist_tags,
    $container('appConfig')->folders->dist_content_images,
]);
