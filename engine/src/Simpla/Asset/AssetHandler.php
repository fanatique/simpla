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

class AssetHandler
{
    /**
     * @see https://stackoverflow.com/a/2050909
     */
    public function copyRecursively(string $sourceFolder, string $distFolder): void
    {
        if (!is_dir($sourceFolder)) {
            throw new \InvalidArgumentException('Source folder does not exist: ' . $sourceFolder);
        }

        $this->createDirectoryRecursively($distFolder);
        if (!is_writable($distFolder)) {
            throw new \InvalidArgumentException('Destination is not writable: ' . $distFolder);
        }

        $dir = opendir($sourceFolder);
        if ($dir === false) {
            throw new \InvalidArgumentException('Cannot open source folder: ' . $sourceFolder);
        }

        while (false !== ($file = readdir($dir))) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $src = $sourceFolder . '/' . $file;
            $dst = $distFolder . '/' . $file;
            if (is_dir($src)) {
                $this->copyRecursively($src, $dst);
            } else {
                if (!copy($src, $dst)) {
                    throw new \RuntimeException('Failed to copy file: ' . $src . ' -> ' . $dst);
                }
            }
        }
        closedir($dir);
    }

    /**
    * Recursively deletes a directory tree.
    *
    * @param string $folder         The directory path.
    * @param bool   $keepRootFolder Whether to keep the top-level folder.
    *
    * @return bool TRUE on success, otherwise FALSE.
    * @see https://gist.github.com/mindplay-dk/a4aad91f5a4f1283a5e2
    */
    public function deleteTree($folder, $keepRootFolder = true): bool
    {
        // Handle bad arguments.
        if (empty($folder) || !file_exists($folder)) {
            return true; // No such file/folder exists.
        } elseif (is_file($folder) || is_link($folder)) {
            return @unlink($folder); // Delete file/link.
        }

        // Delete all children.
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($folder, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $action = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            if (!@$action($fileinfo->getRealPath())) {
                return false; // Abort due to the failure.
            }
        }

        // Delete the root folder itself?
        return (!$keepRootFolder ? @rmdir($folder) : true);
    }

    public function removeAndCreateFolders(array $folders): void
    {
        foreach ($folders as $folder) {
            if (!is_writable(dirname($folder))) {
                throw new \InvalidArgumentException($folder . ' is not writeable.');
            }

            if (is_dir($folder)) {
                $this->deleteTree($folder);
            }
            
            $this->createDirectoryRecursively($folder);
        }
    }
    
    public function createDirectoryRecursively(string $folder): void
    {
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }
    }

    public function persistContent(string $content, string $targetDir, string $slug, string $fileExtension): void
    {
        $this->createDirectoryRecursively($targetDir);
        $filename = $targetDir . '/' . $slug . '.' . $fileExtension;
        if (file_put_contents($filename, $content) === false) {
            throw new \RuntimeException('Failed to write content to ' . $filename);
        }
    }
}
