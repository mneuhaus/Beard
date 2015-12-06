<?php
namespace Famelo\Beard\Utility;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "famelo/beard".          *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 */
class String {

	/**
	 * turn a form name into a path with a dot as seperation
	 * foo[bar][baz] -> foo.bar.baz
	 *
	 * @param string $formName
	 * @return string
	 */
	public static function formNameToPath($formName) {
		$parts = explode('[', $formName);
		array_walk($parts, function(&$value, $key){
			$value = trim($value, ']');
		});
		return implode('.', $parts);
	}

	/**
	 * turn a dot seperated path into a form name
	 * foo.bar.baz -> foo[bar][baz]
	 *
	 * @param string $path
	 * @return string
	 */
	public static function pathToformName($path) {
		$parts = explode('.', $path);
		array_walk($parts, function(&$value, $key){
			$value = '[' . $value . ']';
		});
		$parts[0] = trim($parts[0], '[]');
		return implode('', $parts);
	}

	/**
	 * turn a path into a - seperated id
	 * foo.bar.baz -> foo-bar-baz
	 *
	 * @param string $path
	 * @return string
	 */
    public static function pathToFormId($path) {
        return str_replace('.', '-', $path);
    }

	/**
	 * convert a underscore cased string to a camelcased string
	 *
	 * @param string $string
	 * @return string
	 */
    public static function underscoreToCamelcase($string) {
        return preg_replace_callback(
            "/(^|_)([a-z])/",
            function($word) {
                return strtoupper("$word[2]");
            },
            $string
        );
    }

	/**
	 * convert a camelcased string to a underscore string
	 *
	 * @param string $string
	 * @return string
	 */
    public static function camelcaseToUnderscore($string) {
        return preg_replace_callback(
           "/(^|[a-z])([A-Z])/",
            function($word) {
                return strtolower(strlen($word[1]) ? "$word[1]_$word[2]" : "$word[2]");
            },
            $string
        );
    }

	/**
	 * convert a path into a translation id
	 *
	 * @param string $path
	 * @return string
	 */
	public static function pathToTranslationId($path) {
		return preg_replace('/\.[0-9]*\./', '.', $path);
	}

	/**
	 * cut a suffix from the end of a string
	 *
	 * @param string $string
	 * @param string $suffix
	 * @return string
	 */
    public static function cutSuffix($string, $suffix) {
        if (!static::endsWith($string, $suffix)) {
            return $string;
        }
        return substr($string, 0, strlen($suffix) * -1);
    }

	/**
	 * add a suffix to the end of a string
	 *
	 * @param string $string
	 * @param string $suffix
	 * @return string
	 */
    public static function addSuffix($string, $suffix) {
        if (static::endsWith($string, $suffix)) {
            $string = static::cutSuffix($string, $suffix);
        }
        return $string . $suffix;
    }

	/**
	 * check if a string ends with a specific suffix
	 *
	 * @param string $string
	 * @param string $suffix
	 * @return string
	 */
	public static function endsWith($string, $suffix) {
		return substr($string, strlen($suffix) * -1) === $suffix;
	}

	/**
	 * turn an absolute className into a relative one
	 *
	 * @param string $className
	 * @return string
	 */
    public static function relativeClass($className) {
        return trim(str_replace(array('Famelo\Beard\Scaffold', '\\'), array('', '.'), $className), '.');
    }

	/**
	 * turn a relative className into an absolute one
	 *
	 * @param string $className
	 * @return string
	 */
    public static function classNameFromPath($className) {
        return 'Famelo\Beard\Scaffold\\' . str_replace('.', '\\', $className);
    }

	/**
	 * prefix all lines in a string with a specific prefix
	 *
	 * @param string $string
	 * @param string $prefix
	 * @return string
	 */
    public static function prefixLinesWith($string, $prefix) {
        $lines = explode(chr(10), $string);
        foreach ($lines as $key => $line) {
            $lines[$key] = $prefix . $line;
        }
        return implode("\r\n", $lines);
    }
}
