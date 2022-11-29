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

class Section extends AbstractEntity
{
    const TYPE = 'section';

    public function set(string $key, $value): void
    {
        switch ($key) {
            case 'created_at':
                $this->data[$key] = \DateTime::createFromFormat('Y-m-d', $value);
                break;
            default:
                $this->data[$key] = $value;
                break;
        }
    }
}
