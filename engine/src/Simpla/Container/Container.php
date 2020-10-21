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

namespace Simpla\Container;

class Container
{

    protected $store = [];

    public function __set(string $key, $value): void
    {
        $this->store[$key] = $value;
    }

    public function __invoke(string $key, ...$args)
    {
        if (!isset($this->store[$key])) {
            throw new \InvalidArgumentException($key . ' is unknown to the container');
        }

        return is_callable($this->store[$key]) ? call_user_func_array($this->store[$key], $args) : $this->store[$key];
    }
}
