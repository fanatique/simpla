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

namespace Simpla\Asset;

class ImageOptimizer
{
    private AssetHandler $assetHandler;

    public function __construct(AssetHandler $assetHandler)
    {
        $this->assetHandler = $assetHandler;
    }

    public function optimizeDirectory(string $sourceFolder, string $distFolder, object $config): void
    {
        if (!is_dir($sourceFolder)) {
            throw new \InvalidArgumentException('Source folder does not exist: ' . $sourceFolder);
        }

        $options = $this->normalizeOptions($config);

        if ($options['generate_webp'] && !function_exists('imagewebp')) {
            throw new \RuntimeException('GD extension is missing WebP support.');
        }

        $this->assetHandler->createDirectoryRecursively($distFolder);
        $this->processDirectory($sourceFolder, $distFolder, $options);
    }

    private function processDirectory(string $sourceFolder, string $distFolder, array $options): void
    {
        $dir = opendir($sourceFolder);
        if ($dir === false) {
            throw new \InvalidArgumentException('Cannot open source folder: ' . $sourceFolder);
        }

        while (($file = readdir($dir)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $sourcePath = $sourceFolder . '/' . $file;
            $destinationPath = $distFolder . '/' . $file;

            if (is_dir($sourcePath)) {
                $this->assetHandler->createDirectoryRecursively($destinationPath);
                $this->processDirectory($sourcePath, $destinationPath, $options);
                continue;
            }

            if ($this->processImageFile($sourcePath, $destinationPath, $options)) {
                continue;
            }

            if (!copy($sourcePath, $destinationPath)) {
                throw new \RuntimeException('Failed to copy file: ' . $sourcePath . ' -> ' . $destinationPath);
            }
        }

        closedir($dir);
    }

    private function processImageFile(string $sourcePath, string $destinationPath, array $options): bool
    {
        $imageInfo = @getimagesize($sourcePath);
        if ($imageInfo === false || !isset($imageInfo['mime'])) {
            return false;
        }

        $mime = $imageInfo['mime'];
        $supportedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mime, $supportedMimes, true)) {
            return false;
        }

        [$sourceWidth, $sourceHeight] = $imageInfo;
        [$targetWidth, $targetHeight] = $this->calculateTargetSize(
            $sourceWidth,
            $sourceHeight,
            $options['max_width'],
            $options['max_height']
        );

        $sourceImage = $this->createImageResource($sourcePath, $mime);
        if ($sourceImage === null) {
            throw new \RuntimeException('Unable to create image resource for ' . $sourcePath);
        }

        if (function_exists('imagepalettetotruecolor')) {
            imagepalettetotruecolor($sourceImage);
        }

        $needsResize = $sourceWidth !== $targetWidth || $sourceHeight !== $targetHeight;
        $workingImage = $needsResize
            ? $this->resampleImage($sourceImage, $sourceWidth, $sourceHeight, $targetWidth, $targetHeight)
            : $sourceImage;

        $pathInfo = pathinfo($destinationPath);
        $extension = strtolower($pathInfo['extension'] ?? '');

        if (!$this->writeFallback($workingImage, $mime, $destinationPath, $options)) {
            throw new \RuntimeException('Failed to write optimized image: ' . $destinationPath);
        }

        if ($options['generate_webp'] && $extension !== 'webp') {
            $webpPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.webp';
            $this->writeWebp($workingImage, $webpPath, $options['webp_quality']);
        }

        if ($needsResize) {
            imagedestroy($workingImage);
        }
        imagedestroy($sourceImage);

        return true;
    }

    private function calculateTargetSize(int $width, int $height, ?int $maxWidth, ?int $maxHeight): array
    {
        if (($maxWidth === null || $width <= $maxWidth) && ($maxHeight === null || $height <= $maxHeight)) {
            return [$width, $height];
        }

        $ratio = 1.0;
        if ($maxWidth !== null) {
            $ratio = min($ratio, $maxWidth / $width);
        }
        if ($maxHeight !== null) {
            $ratio = min($ratio, $maxHeight / $height);
        }

        $targetWidth = max(1, (int) floor($width * $ratio));
        $targetHeight = max(1, (int) floor($height * $ratio));

        return [$targetWidth, $targetHeight];
    }

    private function createImageResource(string $path, string $mime): ?\GdImage
    {
        return match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null,
            default => null,
        };
    }

    private function resampleImage(
        \GdImage $sourceImage,
        int $sourceWidth,
        int $sourceHeight,
        int $targetWidth,
        int $targetHeight
    ): \GdImage {
        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);

        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);

        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefill($canvas, 0, 0, $transparent);

        if (!imagecopyresampled(
            $canvas,
            $sourceImage,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $sourceWidth,
            $sourceHeight
        )) {
            throw new \RuntimeException('Failed to resample image to ' . $targetWidth . 'x' . $targetHeight);
        }

        return $canvas;
    }

    private function writeFallback(\GdImage $image, string $mime, string $destinationPath, array $options): bool
    {
        return match ($mime) {
            'image/jpeg' => imagejpeg($image, $destinationPath, $options['jpeg_quality']),
            'image/png' => imagepng($image, $destinationPath, $options['png_compression']),
            'image/webp' => imagewebp($image, $destinationPath, $options['webp_quality']),
            default => false,
        };
    }

    private function writeWebp(\GdImage $image, string $destinationPath, int $quality): void
    {
        if (!imagewebp($image, $destinationPath, $quality)) {
            throw new \RuntimeException('Failed to write WebP file: ' . $destinationPath);
        }
    }

    private function normalizeOptions(object $config): array
    {
        $defaults = [
            'max_width' => null,
            'max_height' => null,
            'generate_webp' => true,
            'webp_quality' => 82,
            'jpeg_quality' => 82,
            'png_compression' => 6,
        ];

        if (isset($config->images)) {
            $defaults['max_width'] = $config->images->max_width ?? null;
            $defaults['max_height'] = $config->images->max_height ?? null;
            $defaults['generate_webp'] = $config->images->generate_webp ?? true;
            $defaults['webp_quality'] = $config->images->webp_quality ?? $defaults['webp_quality'];
            $defaults['jpeg_quality'] = $config->images->jpeg_quality ?? $defaults['jpeg_quality'];
            $defaults['png_compression'] = $config->images->png_compression ?? $defaults['png_compression'];
        }

        return $defaults;
    }
}

