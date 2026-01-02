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

use Simpla\Entity\EntityInterface;
use Simpla\Entity\EntityFactory;

class ContentIterator extends \DirectoryIterator
{
    private $entities = [];
    protected $type;
    protected $entityFactory;

    public function __construct(string $path, string $type, EntityFactory $entityFactory)
    {
        $this->type = $type;
        $this->entityFactory = $entityFactory;
        parent::__construct($path);
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @throws Simpla\Entity\EntityException
     */
    public function getEntity(): EntityInterface
    {
        $entity = $this->entities[$this->getFilename()] ?? $this->loadEntityFromFile();
        return $entity;
    }

    /**
     * @throws Simpla\Entity\EntityException
     */
    public function loadEntityFromFile(): EntityInterface
    {
        $pathToFile = $this->getPath() . \DIRECTORY_SEPARATOR . $this->getFilename();
        $entity = $this->entityFactory->createFromMarkdown($pathToFile, $this->type);

        //add to internal entity cache
        $this->entities[$this->getFilename()] = $entity;

        return $entity;
    }

    /** Tweaking DirectoryIterator::next() to only return real files */
    public function next(): void
    {
        parent::next();

        while ($this->valid() && ($this->isDot() || !$this->isFile())) {
            parent::next();
        }
    }

    /** Tweaking DirectoryIterator::current() to skip . and ..
     */
    public function current(): ContentIterator
    {
        while ($this->valid() && ($this->isDot() || !$this->isFile())) {
            parent::next();
        }
        return parent::current();
    }

    public function sortByEntityTags(): array
    {
        $result = [];
        foreach ($this as $item) {
            if ($item->getEntity()->get('status') === 'draft') {
                continue;
            }
            $entityTags = $item->getEntity()->get('tags');
            foreach ($entityTags as $entityTag) {
                $result[$entityTag][] = $item->getEntity();
            }
        }
        return $result;
    }


    public function findByFieldValue(string $field, string $value): ?EntityInterface
    {
        foreach ($this as $item) {
            if ($item->getEntity()->get($field) === $value && $item->getEntity()->get('status') === 'published') {
                return $item->getEntity();
            }
        }
      
        return null;
    }
}
