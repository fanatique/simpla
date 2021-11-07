<?php declare(strict_types=1);

/*
 * This file is part of fanatique/Simpla.
 *
 * (c) Alexander Thomas <me@alexander-thomas.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

require_once __DIR__ . '/../engine/vendor/autoload.php';
$container = require_once __DIR__ . '/../engine/app/bootstrap_container.php';

function getContentType(string $fileExt): string
{

  switch ($fileExt) {
    case 'png':
      return 'image/png';
    case 'jpg':
    case 'jpeg':
      return 'image/jpeg';
    case 'gif':
      return 'image/gif';
    case 'svg':
      return 'image/svg+xml';
    case 'css':
      return 'text/css';
    case 'js':
      return 'application/javascript';
    case 'json':
      return 'application/json';
    case 'webp':
      return 'image/webp';
    default:
      return 'text/html';
  }

}

$serveIfExists = function (string $fileToServe, string $fileExt, bool $include = true) use ($container)
{
  if (file_exists($fileToServe)) {

    header("Content-type: " . getContentType($fileExt));

    if ($include === true) {

      include $fileToServe;
      return;

    }else {

      print file_get_contents($fileToServe);
      return;

    }

  }

  header("HTTP/1.0 404 Not Found");
  die('File not found');
};

$urlPath = $_SERVER['REQUEST_URI'];
preg_match('/.*\.(.*)/', $urlPath, $matches);
$fileExt = $matches[1] ?? '';

// If url_path is empty, it is root, so call index.php
if (!$urlPath || !preg_match( '/[.]/', $urlPath)) {

  $serveIfExists(__DIR__ . '/index.php', $fileExt);

}


if(preg_match('/^\/css\//', $urlPath) || preg_match( '/\.js$/', $urlPath)) {

  $fileToServe = __DIR__ . '/../page/views/' . $container('config')->theme . '/assets/' . $urlPath;
  $serveIfExists($fileToServe, $fileExt);

}

if(preg_match( '/^\/img\//', $urlPath)) {

  $fileToServe =  __DIR__ . '/../page/content/' . $urlPath;
  $serveIfExists($fileToServe, $fileExt, false);

}

if(preg_match( '/^\/tpl-img\//', $urlPath)) {

  $fileToServe = __DIR__ . '/../page/views/' . $container('config')->theme . '/assets/' . $urlPath;
  $serveIfExists($fileToServe, $fileExt, false);

}

