<?php

namespace Williamsampaio\ArkMigration;

use DateTime;
use DateTimeZone;

class Util
{
    const DATE_FORMAT = 'YmdHis';
    const MIGRATION_FILE_NAME_PATTERN = '/^\d+_([a-z][a-z\d]*(?:_[a-z\d]+)*)\.php$/i';
    const MIGRATION_FILE_NAME_NO_NAME_PATTERN = '/^[0-9]{14}\.php$/';
    const CLASS_NAME_PATTERN = '/^(?:[A-Z][a-z\d]*)+$/';

    public static function getCurrentTimestamp()
    {
        $dt = new DateTime('now', new DateTimeZone('UTC'));

        return $dt->format(static::DATE_FORMAT);
    }

    public static function getExistingMigrationClassNames($path)
    {
        $classNames = [];

        if (!is_dir($path)) {
            return $classNames;
        }

        // filter the files to only get the ones that match our naming scheme
        $phpFiles = static::getFiles($path);

        foreach ($phpFiles as $filePath) {
            $fileName = basename($filePath);
            if (preg_match(static::MIGRATION_FILE_NAME_PATTERN, $fileName)) {
                $classNames[] = static::mapFileNameToClassName($fileName);
            }
        }

        return $classNames;
    }

    public static function mapClassNameToFileName($className)
    {
        $snake = function ($matches) {
            return '_' . strtolower($matches[0]);
        };
        $fileName = preg_replace_callback('/\d+|[A-Z]/', $snake, $className);
        $fileName = static::getCurrentTimestamp() . "$fileName.php";

        return $fileName;
    }

    public static function mapFileNameToClassName($fileName)
    {
        $matches = [];
        if (preg_match(static::MIGRATION_FILE_NAME_PATTERN, $fileName, $matches)) {
            $fileName = $matches[1];
        } elseif (preg_match(static::MIGRATION_FILE_NAME_NO_NAME_PATTERN, $fileName)) {
            return 'V' . substr($fileName, 0, strlen($fileName) - 4);
        }

        $className = str_replace('_', '', ucwords($fileName, '_'));

        return $className;
    }

    public static function isUniqueMigrationClassName($className, $path)
    {
        $existingClassNames = static::getExistingMigrationClassNames($path);

        return !in_array($className, $existingClassNames, true);
    }

    public static function isValidPhinxClassName($className)
    {
        return (bool)preg_match(static::CLASS_NAME_PATTERN, $className);
    }

    public static function globAll(array $paths)
    {
        $result = [];

        foreach ($paths as $path) {
            $result = array_merge($result, static::glob($path));
        }

        return $result;
    }

    public static function glob($path)
    {
        return glob($path, defined('GLOB_BRACE') ? GLOB_BRACE : 0);
    }

    public static function getFiles($paths)
    {
        $files = static::globAll(array_map(function ($path) {
            return $path . DIRECTORY_SEPARATOR . '*.php';
        }, (array)$paths));
        // glob() can return the same file multiple times
        // This will cause the migration to fail with a
        // false assumption of duplicate migrations
        // https://php.net/manual/en/function.glob.php#110340
        $files = array_unique($files);

        return $files;
    }
}
