<?php
namespace Famelo\Beard\Utility;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "famelo/beard".       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 */
class Path {

    /**
     * take any number of paths as agument and safely join them without duplicating
     * path seperators, etc
     */
    public static function joinPaths() {
        $paths = func_get_args();
        $prefix = '';
        $suffix = '';
        if (substr(current($paths), 0, 1) == DIRECTORY_SEPARATOR) {
            $prefix = DIRECTORY_SEPARATOR;
        }
        if (substr(end($paths), -1, 1) == DIRECTORY_SEPARATOR) {
            $suffix = DIRECTORY_SEPARATOR;
        }
        foreach ($paths as $key => $path) {
            if ($path === NULL) {
                unset($paths[$key]);
                continue;
            }
            $paths[$key] = trim($path, DIRECTORY_SEPARATOR);
        }
        $path = $prefix . implode(DIRECTORY_SEPARATOR, $paths) . $suffix;
        return $path;
    }

    /**
     * check if both paths are the same, without the need for the paths to exist
     */
    public static function isIdentical($left, $right) {
        return static::realpath($left) == static::realpath($right);
    }

    /**
     * This function is to replace PHP's extremely buggy realpath().
     *
     * @param string The original path, can be relative etc.
     * @return string The resolved path, it might not exist.
     */
    public static function realpath($path) {
        // whether $path is unix or not
        $unipath=strlen($path)==0 || $path{0}!='/';
        // attempts to detect if path is relative in which case, add cwd
        if(strpos($path,':')===false && $unipath)
            $path=getcwd().DIRECTORY_SEPARATOR.$path;
        // resolve path parts (single dot, double dot and double delimiters)
        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = array();
        foreach ($parts as $part) {
            if ('.'  == $part) continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        $path=implode(DIRECTORY_SEPARATOR, $absolutes);
        // resolve any symlinks
        if(file_exists($path) && linkinfo($path)>0)$path=readlink($path);
        // put initial separator that could have been lost
        $path=!$unipath ? '/'.$path : $path;
        return '/' . trim($path, '/');
    }

	/**
	 * turn an absolute path into a relative one
	 *
	 * @param string $absolutePath
	 * @return string
	 */
    public static function relativePath($absolutePath) {
        return trim(str_replace(getcwd(), '', $absolutePath), '/');
    }
}
?>
