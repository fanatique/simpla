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

use Simpla\Markdown\MarkdownParser;

class EntityFactory
{
    protected MarkdownParser $markdownParser;

    public function __construct(MarkdownParser $markdownParser)
    {
        $this->markdownParser = $markdownParser;
    }

    public function createFromMarkdown(string $pathToFile, string $type): EntityInterface
    {
        if (!is_file($pathToFile) || !is_readable($pathToFile)) {
            throw new EntityException(static::class . ': could not read markdown file at ' . $pathToFile);
        }

        $fileContents = file_get_contents($pathToFile);
        if ($fileContents === false) {
            throw new EntityException(static::class . ': failed to load markdown file at ' . $pathToFile);
        }

        $entityData = $this->markdownParser->meta($fileContents);
        $entityData['content'] = $this->markdownParser->text($fileContents);

        $this->assertRequiredFields($entityData, $pathToFile);

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
        default:
          throw new EntityException(static::class . ': could not create Entity. ' . $type . ' is invalid.');
        break;
    }
        return $entity;
    }

    private function assertRequiredFields(array $entityData, string $path): void
    {
        $required = ['title', 'status', 'created_at', 'content'];
        foreach ($required as $key) {
            if (!array_key_exists($key, $entityData) || $entityData[$key] === null || $entityData[$key] === '') {
                throw new EntityException(static::class . ": missing required field '{$key}' in {$path}");
            }
        }
    }
}
