<?php

declare(strict_types=1);

namespace Simpla\Content;

trait TemplateRendererTrait
{
    /**
     * Render a PHP template with a controlled variable scope.
     *
     * @param array<string,mixed> $vars Variables exposed to the template.
     */
    private function renderTemplate(string $path, array $vars = []): string
    {
        if (!is_file($path)) {
            throw new \RuntimeException('Template not found: ' . $path);
        }

        if (!empty($vars)) {
            extract($vars, EXTR_SKIP);
        }

        ob_start();
        include $path;
        return (string) ob_get_clean();
    }
}

