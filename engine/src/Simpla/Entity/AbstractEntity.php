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

abstract class AbstractEntity implements EntityInterface
{
    protected $data = [];

    private function __construct()
    {
    }

    public static function createFromArray(array $data): self
    {
        if (!isset($data['title'], $data['status'], $data['created_at'], $data['content'])) {
            throw new EntityException('Cannot create entity from array. One or more keys are missing.');
        }

        $instance = new static();
        foreach ($data as $key => $value) {
            $instance->set($key, $value);
        }

        return $instance;
    }

    abstract public function set(string $key, $value): void;

    public function get($identifier)
    {
        if (!isset($this->data[$identifier])) {
            return null;
        }

        return $this->data[$identifier];
    }

    public function getSlug(string $baseURL = ''): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $this->data['title']), '-'));

        return $baseURL . '/' . $slug;
    }
}
