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

namespace Simpla\Entity;

use Pagerange\Markdown\MetaParsedown;

class EntityFactory
{
    protected $markdownParser;

    public function __construct(MetaParsedown $markdownParser)
    {
        $this->markdownParser = $markdownParser;
    }

    public function createFromMarkdown(string $pathToFile, string $type): EntityInterface
    {
        $fileContents = file_get_contents($pathToFile);
        $entityData = $this->markdownParser->meta($fileContents);
        $entityData['content'] = $this->markdownParser->text($fileContents);

        switch ($type) {
        case Post::TYPE:
          $entity = Post::createFromArray($entityData);
        break;
        case Page::TYPE:
          $entity = Page::createFromArray($entityData);
        break;
        case Snippet::TYPE:
          $entity = Snippet::createFromArray($entityData);
        break;
        case Section::TYPE:
          $entity = Section::createFromArray($entityData);
        break;
        default:
          throw new EntityException(static::class . ': could not create Entity. ' . $type . ' is invalid.');
        break;
    }
        return $entity;
    }
}
