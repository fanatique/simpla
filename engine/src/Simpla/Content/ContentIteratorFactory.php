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

use Simpla\Entity\EntityFactory;
use Simpla\Content\ContentIterator;

class ContentIteratorFactory
{

    protected $entityFactory;

    public function __construct(EntityFactory $entityFactory)
    {
        $this->entityFactory = $entityFactory;
    }

    public function create(string $path, string $type): ContentIterator
    {
        return new ContentIterator($path, $type, $this->entityFactory);
    }
}
