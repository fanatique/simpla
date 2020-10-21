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

interface EntityInterface
{
    public static function createFromArray(array $data);

    public function get(string $identifier);

    public function set(string $key, $value): void;

    public function getSlug(string $baseUrl = ''): string;
    
}
