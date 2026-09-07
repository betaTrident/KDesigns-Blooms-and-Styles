<?php
declare(strict_types=1);

final class View
{
    /**
     * Render a template from /views. `$template` is a relative path without `.php`.
     *
     * @param array<string, mixed> $data
     */
    public static function render(string $template, array $data = []): void
    {
        $file = self::resolve($template);

        (static function (string $file, array $data): void {
            extract($data, EXTR_SKIP);
            require $file;
        })($file, $data);
    }

    private static function resolve(string $template): string
    {
        $template = str_replace('\\', '/', $template);
        if ($template === '' || !preg_match('#^[a-zA-Z0-9/_-]+$#', $template)) {
            throw new InvalidArgumentException('Invalid view name.');
        }

        $viewsRoot = KD_ROOT . DIRECTORY_SEPARATOR . 'views';
        $path = $viewsRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $template) . '.php';

        $baseReal = realpath($viewsRoot);
        $fileReal = realpath($path);

        if ($baseReal === false || $fileReal === false) {
            throw new RuntimeException('View not found.');
        }

        $baseNorm = rtrim(str_replace('\\', '/', $baseReal), '/');
        $fileNorm = str_replace('\\', '/', $fileReal);

        if ($fileNorm !== $baseNorm && !str_starts_with($fileNorm, $baseNorm . '/')) {
            throw new RuntimeException('View not found.');
        }

        return $fileReal;
    }
}
