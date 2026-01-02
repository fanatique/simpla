<?php

declare(strict_types=1);

namespace Simpla\Markdown;

/**
 * Lightweight Markdown + frontmatter parser to replace Parsedown/MetaParsedown.
 * Supports:
 * - Frontmatter delimited by --- ... --- at the top of the file (key: value).
 * - Headings (#..######), paragraphs, unordered/ordered lists, fenced code blocks (with language class).
 * - Tables with alignment markers.
 * - Inline links, bold/italic, inline code.
 * - Leaves existing inline HTML untouched.
 */
class MarkdownParser
{
    private const FRONT_MATTER_DELIMITER = '---';
    private const CODE_PLACEHOLDER_PREFIX = "\x00SMP_CODE\x00";

    private bool $escapeHtml;
    private bool $imageLazyLoading;

    /**
     * @param array{image_lazy?: bool} $options
     */
    public function __construct(bool $escapeHtml = false, array $options = [])
    {
        $this->escapeHtml = $escapeHtml;
        $this->imageLazyLoading = $options['image_lazy'] ?? true;
    }

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
        $currentListKey = null;
        foreach ($metaLines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                $currentListKey = null;
                continue;
            }

            if (preg_match('/^-\\s+(.+)$/', $trimmed, $listMatch) && $currentListKey !== null) {
                $meta[$currentListKey][] = $this->parseYamlScalar($listMatch[1]);
                continue;
            }

            if (!preg_match('/^([A-Za-z0-9_-]+)\\s*:\\s*(.*)$/', $trimmed, $matches)) {
                $currentListKey = null;
                continue;
            }

            $key = $matches[1];
            $value = $matches[2];

            if ($value === '') {
                $meta[$key] = [];
                $currentListKey = $key;
                continue;
            }

            $meta[$key] = $this->parseYamlScalar($value);
            $currentListKey = null;
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
        $codeLanguage = null;

        $flushParagraph = function () use (&$paragraphBuffer, &$html) {
            if (count($paragraphBuffer) === 0) {
                return;
            }
            $text = trim(implode(' ', array_map('trim', $paragraphBuffer)));
            $paragraphBuffer = [];
            if ($text === '') {
                return;
            }
            if (!$this->escapeHtml && $this->looksLikeHtml($text)) {
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

        $lineCount = count($lines);
        for ($i = 0; $i < $lineCount; $i++) {
            $line = $lines[$i];
            $trimmed = trim($line);

            if ($inCodeBlock) {
                if (preg_match('/^```/', $trimmed)) {
                    $html[] = $this->renderCodeBlock($codeBuffer, $codeLanguage);
                    $codeBuffer = [];
                    $codeLanguage = null;
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

            if ($this->isTableHeader($trimmed) && $i + 1 < $lineCount && $this->isTableSeparator(trim($lines[$i + 1]))) {
                $flushParagraph();
                $flushList();

                $headers = $this->parseTableRow($line);
                [$alignments, $separatorColumnCount] = $this->parseTableSeparator($lines[$i + 1]);
                $i += 1;

                $rows = [];
                $rowIndex = $i + 1;
                while ($rowIndex < $lineCount) {
                    $rowLine = $lines[$rowIndex];
                    $rowTrimmed = trim($rowLine);
                    if ($rowTrimmed === '' || !$this->looksLikeTableRow($rowTrimmed)) {
                        break;
                    }
                    $rows[] = $this->parseTableRow($rowLine);
                    $rowIndex++;
                }

                $i = $rowIndex - 1;
                $columnCount = max(count($headers), $separatorColumnCount);
                $html[] = $this->renderTable($headers, $rows, $alignments, $columnCount);
                continue;
            }

            if (preg_match('/^```\\s*([A-Za-z0-9_+\\.-]+)?\\s*$/', $trimmed, $matches)) {
                $flushParagraph();
                $flushList();
                $inCodeBlock = true;
                $codeBuffer = [];
                $codeLanguage = isset($matches[1]) && $matches[1] !== '' ? strtolower($matches[1]) : null;
                continue;
            }

            if (preg_match('/^(#{1,6})\\s*(.+)$/', $trimmed, $matches)) {
                $flushParagraph();
                $flushList();
                $level = strlen($matches[1]);
                $headingText = $matches[2];
                $text = $this->renderInline($headingText);
                $idAttribute = '';
                if ($level <= 3) {
                    $id = $this->slugifyHeading($headingText);
                    if ($id !== '') {
                        $idAttribute = ' id="' . $id . '"';
                    }
                }
                $html[] = sprintf('<h%d%s>%s</h%d>', $level, $idAttribute, $text, $level);
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
            $html[] = $this->renderCodeBlock($codeBuffer, $codeLanguage);
        }

        return implode("\n", $html);
    }

    private function slugifyHeading(string $headingText): string
    {
        // Normalize inline markdown to plain text and generate a URL-safe slug.
        $renderedInline = $this->renderInline($headingText);
        $plainText = trim(strip_tags($renderedInline));
        $decoded = html_entity_decode($plainText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $lower = function_exists('mb_strtolower') ? mb_strtolower($decoded, 'UTF-8') : strtolower($decoded);
        $sanitized = preg_replace('/[^\\p{L}\\p{N}\\s-]/u', '', $lower) ?? '';
        $collapsed = preg_replace('/[\\s_-]+/u', '-', $sanitized) ?? '';
        return trim($collapsed, '-');
    }

    private function looksLikeHtml(string $text): bool
    {
        return (bool) preg_match('/^<[^>]+>.*<\\/[^>]+>$/s', $text);
    }

    private function renderInline(string $text): string
    {
        $codePlaceholders = [];
        $textWithPlaceholders = preg_replace_callback('/`([^`]+)`/', function ($matches) use (&$codePlaceholders) {
            $token = self::CODE_PLACEHOLDER_PREFIX . count($codePlaceholders) . "\x00";
            $codePlaceholders[$token] = '<code>' . htmlspecialchars($matches[1], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</code>';
            return $token;
        }, $text) ?? $text;

        if ($this->escapeHtml) {
            $textWithPlaceholders = htmlspecialchars($textWithPlaceholders, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        // Images (optional {picture ...} attribute block to emit <picture> with WebP source)
        $textWithPlaceholders = preg_replace_callback(
            '/!\\[([^\\]]*)\\]\\(([^)\\s]+)(?:\\s+"([^"]+)")?\\)\\s*(\\{[^}]*\\})?/',
            function ($matches) {
                $alt = $matches[1];
                $src = $matches[2];
                $title = $matches[3] ?? null;
                $attrBlock = $matches[4] ?? null;
                return $this->renderImage($alt, $src, $title, $attrBlock);
            },
            $textWithPlaceholders
        ) ?? $textWithPlaceholders;

        // Bold: **text** and __text__
        $textWithPlaceholders = preg_replace('/\\*\\*(.+?)\\*\\*/s', '<strong>$1</strong>', $textWithPlaceholders) ?? $textWithPlaceholders;
        $textWithPlaceholders = preg_replace('/(?<![\\w])__(.+?)__(?![\\w])/s', '<strong>$1</strong>', $textWithPlaceholders) ?? $textWithPlaceholders;

        // Italic: *text* and _text_
        $textWithPlaceholders = preg_replace('/\\*(.+?)\\*/s', '<em>$1</em>', $textWithPlaceholders) ?? $textWithPlaceholders;
        $textWithPlaceholders = preg_replace('/(?<![\\w])_(.+?)_(?![\\w])/s', '<em>$1</em>', $textWithPlaceholders) ?? $textWithPlaceholders;

        // Links with optional title
        $textWithPlaceholders = preg_replace_callback('/\\[([^\\]]+)\\]\\(([^)\\s]+)(?:\\s+"([^"]+)")?\\)/', function ($matches) {
            $label = $matches[1];
            $url = htmlspecialchars($matches[2], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $title = isset($matches[3]) ? ' title="' . htmlspecialchars($matches[3], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"' : '';
            return '<a href="' . $url . '"' . $title . '>' . $label . '</a>';
        }, $textWithPlaceholders) ?? $textWithPlaceholders;

        foreach ($codePlaceholders as $token => $codeHtml) {
            $textWithPlaceholders = str_replace($token, $codeHtml, $textWithPlaceholders);
        }

        return $textWithPlaceholders;
    }

    private function renderImage(string $alt, string $src, ?string $title, ?string $attrBlock): string
    {
        $attrs = $this->parseImageAttributes($attrBlock);

        $altEscaped = htmlspecialchars($alt, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $srcEscaped = htmlspecialchars($src, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $titleAttr = $title !== null ? ' title="' . htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"' : '';
        $widthAttr = $attrs['width'] !== null ? ' width="' . htmlspecialchars($attrs['width'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"' : '';
        $heightAttr = $attrs['height'] !== null ? ' height="' . htmlspecialchars($attrs['height'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"' : '';
        $classAttr = $attrs['class'] !== null ? ' class="' . htmlspecialchars($attrs['class'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"' : '';
        $loadingValue = $attrs['loading'] ?? ($this->imageLazyLoading ? 'lazy' : null);
        $loadingAttr = $loadingValue !== null ? ' loading="' . htmlspecialchars($loadingValue, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"' : '';

        $imgTag = '<img src="' . $srcEscaped . '" alt="' . $altEscaped . '"' . $titleAttr . $widthAttr . $heightAttr . $classAttr . $loadingAttr . '>';

        if (!$attrs['picture']) {
            return $imgTag;
        }

        $sources = [];
        if ($attrs['webp'] !== null) {
            $webpEscaped = htmlspecialchars($attrs['webp'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $sources[] = '<source srcset="' . $webpEscaped . '" type="image/webp">';
        }

        if (count($sources) === 0) {
            return $imgTag;
        }

        return '<picture>' . implode('', $sources) . $imgTag . '</picture>';
    }

    private function parseImageAttributes(?string $attrBlock): array
    {
        $attributes = [
            'picture' => false,
            'webp' => null,
            'width' => null,
            'height' => null,
            'class' => null,
            'loading' => null,
        ];

        if ($attrBlock === null) {
            return $attributes;
        }

        $content = trim($attrBlock, "{} \t\n\r\0\x0B");
        if ($content === '') {
            return $attributes;
        }

        $tokens = preg_split('/\\s+/', $content) ?: [];
        foreach ($tokens as $token) {
            if ($token === '') {
                continue;
            }

            if ($token === 'picture') {
                $attributes['picture'] = true;
                continue;
            }

            if (!preg_match('/^([A-Za-z0-9_-]+)=(?:"([^"]*)"|\'([^\']*)\')$/', $token, $matches)) {
                continue;
            }

            $key = $matches[1];
            $value = $matches[2] !== '' ? $matches[2] : $matches[3];

            if (array_key_exists($key, $attributes)) {
                $attributes[$key] = $value;
            }
        }

        return $attributes;
    }

    private function parseYamlScalar(string $value): mixed
    {
        $trimmed = trim($value);

        if ($trimmed === 'null' || $trimmed === '~') {
            return null;
        }

        $lower = strtolower($trimmed);
        if ($lower === 'true') {
            return true;
        }
        if ($lower === 'false') {
            return false;
        }

        if (is_numeric($trimmed)) {
            return str_contains($trimmed, '.') ? (float) $trimmed : (int) $trimmed;
        }

        if ((str_starts_with($trimmed, "'") && str_ends_with($trimmed, "'")) ||
            (str_starts_with($trimmed, '"') && str_ends_with($trimmed, '"'))
        ) {
            return substr($trimmed, 1, -1);
        }

        if (str_starts_with($trimmed, '[') && str_ends_with($trimmed, ']')) {
            $inner = substr($trimmed, 1, -1);
            if (trim($inner) === '') {
                return [];
            }
            $parts = array_map('trim', explode(',', $inner));
            $list = [];
            foreach ($parts as $part) {
                if ($part === '') {
                    continue;
                }
                $list[] = $this->parseYamlScalar($part);
            }
            return $list;
        }

        return $trimmed;
    }

    private function renderCodeBlock(array $codeBuffer, ?string $codeLanguage): string
    {
        $class = $codeLanguage !== null ? ' class="language-' . htmlspecialchars($codeLanguage, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"' : '';
        return '<pre><code' . $class . '>' . htmlspecialchars(implode("\n", $codeBuffer), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</code></pre>';
    }

    private function isTableHeader(string $line): bool
    {
        return $this->looksLikeTableRow($line);
    }

    private function isTableSeparator(string $line): bool
    {
        $cells = array_filter(array_map('trim', explode('|', trim($line, '|'))), fn ($cell) => $cell !== '');
        if (count($cells) === 0) {
            return false;
        }

        foreach ($cells as $cell) {
            if (!preg_match('/^:?-{3,}:?$/', $cell)) {
                return false;
            }
        }

        return true;
    }

    private function parseTableSeparator(string $line): array
    {
        $cells = array_filter(array_map('trim', explode('|', trim($line, '|'))), fn ($cell) => $cell !== '');
        $alignments = [];

        foreach ($cells as $cell) {
            $alignments[] = $this->alignmentFromCell($cell);
        }

        return [$alignments, count($cells)];
    }

    private function looksLikeTableRow(string $line): bool
    {
        $cells = array_filter(array_map('trim', explode('|', trim($line, '|'))), fn ($cell) => $cell !== '');
        return count($cells) >= 2;
    }

    private function parseTableRow(string $line): array
    {
        $cells = array_map('trim', explode('|', trim($line, '|')));
        return $cells;
    }

    private function renderTable(array $headers, array $rows, array $alignments, int $columnCount): string
    {
        $normalizedHeaders = $this->normalizeTableRow($headers, $columnCount);
        $renderedHeaders = array_map(function ($cell, $index) use ($alignments) {
            return '<th' . $this->alignmentAttribute($alignments[$index] ?? 'left') . '>' . $this->renderInline($cell) . '</th>';
        }, $normalizedHeaders, array_keys($normalizedHeaders));

        $renderedRows = [];
        foreach ($rows as $row) {
            $normalizedRow = $this->normalizeTableRow($row, $columnCount);
            $cells = [];
            foreach ($normalizedRow as $index => $cell) {
                $cells[] = '<td' . $this->alignmentAttribute($alignments[$index] ?? 'left') . '>' . $this->renderInline($cell) . '</td>';
            }
            $renderedRows[] = '<tr>' . implode('', $cells) . '</tr>';
        }

        return '<table><thead><tr>' . implode('', $renderedHeaders) . '</tr></thead><tbody>' . implode('', $renderedRows) . '</tbody></table>';
    }

    private function alignmentFromCell(string $cell): string
    {
        $startsWithColon = str_starts_with($cell, ':');
        $endsWithColon = str_ends_with($cell, ':');

        if ($startsWithColon && $endsWithColon) {
            return 'center';
        }

        if ($endsWithColon) {
            return 'right';
        }

        return 'left';
    }

    private function alignmentAttribute(string $alignment): string
    {
        if ($alignment === 'center') {
            return ' style="text-align:center"';
        }
        if ($alignment === 'right') {
            return ' style="text-align:right"';
        }
        return '';
    }

    private function normalizeTableRow(array $row, int $columnCount): array
    {
        $normalized = $row;
        if (count($normalized) < $columnCount) {
            $normalized = array_pad($normalized, $columnCount, '');
        }
        if (count($normalized) > $columnCount) {
            $normalized = array_slice($normalized, 0, $columnCount);
        }
        return $normalized;
    }
}

