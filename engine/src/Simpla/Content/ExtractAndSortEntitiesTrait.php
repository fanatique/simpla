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

use Simpla\Content\ContentIterator;

trait ExtractAndSortEntitiesTrait
{
    private function extractAndSortEntities(ContentIterator $contentItems): array
    {
        $entities = [];
        /** @var $content ContentIterator */
        foreach ($contentItems as $content) {
            $entity = $content->getEntity();
            if ($entity->get('status') !== 'published') {
                continue;
            }
            $key = $entity->get('created_at')->format('Y-m-d') . '-' . random_int(0, 100);
            $entities[$key] = $entity;
        }
        uksort($entities, function ($time1, $time2) {
            if (strtotime($time1) < strtotime($time2)) {
                return 1;
            } elseif (strtotime($time1) > strtotime($time2)) {
                return -1;
            } else {
                return 0;
            }
        });
        return $entities;
    }
}
