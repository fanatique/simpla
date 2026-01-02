<?php

declare(strict_types=1);

namespace Simpla\Markdown;

/**
 * Lightweight Markdown + frontmatter parser to replace Parsedown/MetaParsedown.
 * Supports:
 * - Frontmatter delimited by --- ... --- at the top of the file (key: value).
 * - Headings (#..######), paragraphs, unordered/ordered lists, fenced code blocks.
 * - Inline links, bold/italic, inline code.
 * - Leaves existing inline HTML untouched.
 */
class MarkdownParser
{
    private const FRONT_MATTER_DELIMITER = '---';

    public function meta(string $markdown): array
    {
        [$meta] = $this->splitFrontMatter($markdown);
        return $meta;
    }

    public function text(string $markdown): string
    {
        [$meta, $body] = $this->splitFrontMatter($markdown);
        return $this->renderMarkdown($body);
    }

    /**
     * @return array{0: array<string, mixed>, 1: string}
     */
    private function splitFrontMatter(string $markdown): array
    {
        $lines = preg_split('/\\r\\n|\\n|\\r/', $markdown) ?: [];
        if (count($lines) === 0 || trim($lines[0]) !== self::FRONT_MATTER_DELIMITER) {
            return [[], $markdown];
        }

        $metaLines = [];
        $bodyLines = [];
        $inMeta = true;

        for ($i = 1, $len = count($lines); $i < $len; $i++) {
            $line = $lines[$i];
            if ($inMeta && trim($line) === self::FRONT_MATTER_DELIMITER) {
                $inMeta = false;
                $bodyLines = array_slice($lines, $i + 1);
                break;
            }
            if ($inMeta) {
                $metaLines[] = $line;
            }
        }

        // If the closing delimiter was never found, treat everything as body.
        if ($inMeta) {
            return [[], $markdown];
        }

        return [$this->parseMetaLines($metaLines), implode("\n", $bodyLines)];
    }

    private function parseMetaLines(array $metaLines): array
    {
        $meta = [];
        foreach ($metaLines as $line) {
            if (trim($line) === '') {
                continue;
            }
            if (!preg_match('/^\\s*([A-Za-z0-9_-]+)\\s*:\\s*(.*)$/', $line, $matches)) {
                continue;
            }
            $key = $matches[1];
            $value = trim($matches[2]);
            if ($value === '') {
                $meta[$key] = '';
                continue;
            }

            // Strip surrounding single/double quotes if present.
            if ((str_starts_with($value, "'") && str_ends_with($value, "'")) ||
                (str_starts_with($value, '"') && str_ends_with($value, '"'))
            ) {
                $value = substr($value, 1, -1);
            }

            $meta[$key] = $value;
        }

        return $meta;
    }

    private function renderMarkdown(string $markdown): string
    {
        $lines = preg_split('/\\r\\n|\\n|\\r/', $markdown) ?: [];
        $html = [];
        $paragraphBuffer = [];
        $listBuffer = [];
        $listType = null;
        $inCodeBlock = false;
        $codeBuffer = [];

        $flushParagraph = function () use (&$paragraphBuffer, &$html) {
            if (count($paragraphBuffer) === 0) {
                return;
            }
            $text = trim(implode(' ', array_map('trim', $paragraphBuffer)));
            $paragraphBuffer = [];
            if ($text === '') {
                return;
            }
            if ($this->looksLikeHtml($text)) {
                $html[] = $text;
            } else {
                $html[] = '<p>' . $this->renderInline($text) . '</p>';
            }
        };

        $flushList = function () use (&$listBuffer, &$listType, &$html) {
            if ($listType === null || count($listBuffer) === 0) {
                $listBuffer = [];
                $listType = null;
                return;
            }
            $items = array_map(fn ($item) => '<li>' . $this->renderInline($item) . '</li>', $listBuffer);
            $html[] = '<' . $listType . '>' . implode('', $items) . '</' . $listType . '>';
            $listBuffer = [];
            $listType = null;
        };

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($inCodeBlock) {
                if (preg_match('/^```/', $trimmed)) {
                    $html[] = '<pre><code>' . htmlspecialchars(implode("\n", $codeBuffer), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</code></pre>';
                    $codeBuffer = [];
                    $inCodeBlock = false;
                    continue;
                }
                $codeBuffer[] = $line;
                continue;
            }

            if ($trimmed === '') {
                $flushParagraph();
                $flushList();
                continue;
            }

            if (preg_match('/^```/', $trimmed)) {
                $flushParagraph();
                $flushList();
                $inCodeBlock = true;
                $codeBuffer = [];
                continue;
            }

            if (preg_match('/^(#{1,6})\\s*(.+)$/', $trimmed, $matches)) {
                $flushParagraph();
                $flushList();
                $level = strlen($matches[1]);
                $text = $this->renderInline($matches[2]);
                $html[] = sprintf('<h%d>%s</h%d>', $level, $text, $level);
                continue;
            }

            if (preg_match('/^[-*]\\s+(.+)$/', $trimmed, $matches)) {
                $flushParagraph();
                if ($listType !== 'ul') {
                    $flushList();
                    $listType = 'ul';
                }
                $listBuffer[] = $matches[1];
                continue;
            }

            if (preg_match('/^\\d+\\.\\s+(.+)$/', $trimmed, $matches)) {
                $flushParagraph();
                if ($listType !== 'ol') {
                    $flushList();
                    $listType = 'ol';
                }
                $listBuffer[] = $matches[1];
                continue;
            }

            $paragraphBuffer[] = $line;
        }

        $flushParagraph();
        $flushList();

        if ($inCodeBlock && count($codeBuffer) > 0) {
            // Unclosed code block; render what we have.
            $html[] = '<pre><code>' . htmlspecialchars(implode("\n", $codeBuffer), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</code></pre>';
        }

        return implode("\n", $html);
    }

    private function looksLikeHtml(string $text): bool
    {
        return (bool) preg_match('/^<[^>]+>.*<\\/[^>]+>$/s', $text);
    }

    private function renderInline(string $text): string
    {
        // Inline code
        $text = preg_replace_callback('/`([^`]+)`/', function ($matches) {
            return '<code>' . htmlspecialchars($matches[1], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</code>';
        }, $text) ?? $text;

        // Bold
        $text = preg_replace('/\\*\\*(.+?)\\*\\*/s', '<strong>$1</strong>', $text) ?? $text;

        // Italic
        $text = preg_replace('/\\*(.+?)\\*/s', '<em>$1</em>', $text) ?? $text;

        // Links
        $text = preg_replace_callback('/\\[([^\\]]+)\\]\\(([^)]+)\\)/', function ($matches) {
            $label = $matches[1];
            $url = htmlspecialchars($matches[2], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            return '<a href="' . $url . '">' . $label . '</a>';
        }, $text) ?? $text;

        return $text;
    }
}

