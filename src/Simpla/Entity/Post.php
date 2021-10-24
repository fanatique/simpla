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

class Post extends AbstractEntity
{
    const TYPE = 'post';
    const EXCERPT_MAX_LENGTH = 150;

    public function set(string $key, $value): void
    {
        switch ($key) {
            case 'tags':
                $this->setTagsAsString($value);
                break;
            case 'created_at':
                $this->data[$key] = \DateTime::createFromFormat('Y-m-d', $value);
                break;
            default:
                $this->data[$key] = $value;
                break;
        }
    }

    public function getExcerpt(): string
    {
        $plainContent = strip_tags($this->data['content']);
        $words = explode(' ', $plainContent);

        if (count($words) <= self::EXCERPT_MAX_LENGTH) {
            return $this->data['content'];
        }
        $pattern = "/<p>.*<\/p>/";
        preg_match_all($pattern, $this->data['content'], $text);
        $excerpt = array_slice($text[0], 0, 3);
        return implode('', $excerpt);
    }

    protected function setTagsAsString($tags): void
    {
        $tagsArr = isset($tags) ? (array) explode(',', $tags) : [];
        $this->data['tags'] = ($tagsArr > 0) ? array_map('trim', $tagsArr) : null;
    }
}
