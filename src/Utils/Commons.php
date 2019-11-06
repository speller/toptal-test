<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 2019-01-10
 * Time: 09:29
 */

namespace App\Utils;

/**
 * Utilities for basic routines
 */
class Commons
{
    /**
     * Shortcut for object property value extraction.
     * @param \stdClass $object
     * @param string $prop
     * @param null $default Default value
     * @return mixed|null
     */
    public static function valueO(\stdClass $object, string $prop, $default = null)
    {
        return property_exists($object, $prop) ? $object->$prop : $default;
    }

    /**
     * Crates temp file which will be deleted after script executed. Returns file name and file handle.
     * @return array
     * @throws \Throwable
     */
    public static function createTempFileAutoDel(): array
    {
        $tmpFile = tmpfile();
        try {
            $metaData = stream_get_meta_data($tmpFile);
            $fileName = $metaData['uri'];
            return [$fileName, $tmpFile];
        } catch (\Throwable $e) {
            fclose($tmpFile);
            throw $e;
        }
    }

    /**
     * Remove directory with all the contents
     * @param string $dir
     */
    public static function removeDirWithFiles(string $dir): void
    {
        foreach (glob($dir) as $file) {
            if (is_dir($file)) {
                self::removeDirWithFiles("$file/*");
                rmdir($file);
            } else {
                unlink($file);
            }
        }
    }
}