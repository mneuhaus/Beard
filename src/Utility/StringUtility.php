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
class StringUtility {

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

    /**
	 * @var string
	 */
	static protected $phoenticReplacements = array(
        '°' => '0',
        '¹' => '1',
        '²' => '2',
        '³' => '3',
        'º' => 'o',
        'æ' => 'ae',
        'ǽ' => 'ae',
        'À' => 'A',
        'Á' => 'A',
        'Â' => 'A',
        'Ã' => 'A',
        'Å' => 'A',
        'Ǻ' => 'A',
        'Ă' => 'A',
        'Ǎ' => 'A',
        'Æ' => 'AE',
        'Ǽ' => 'AE',
        'à' => 'a',
        'á' => 'a',
        'â' => 'a',
        'ã' => 'a',
        'å' => 'a',
        'ǻ' => 'a',
        'ă' => 'a',
        'ǎ' => 'a',
        'ª' => 'a',
        '@' => 'at',
        '€' => 'euros',
        '$' => 'dollars',
        '£' => 'pounds',
        'Ĉ' => 'C',
        'Ċ' => 'C',
        'ĉ' => 'c',
        'ċ' => 'c',
        '©' => 'c',
        'Ð' => 'D',
        'Đ' => 'D',
        'ð' => 'dj',
        'đ' => 'd',
        'È' => 'E',
        'É' => 'E',
        'Ê' => 'E',
        'Ë' => 'E',
        'Ĕ' => 'E',
        'Ė' => 'E',
        'è' => 'e',
        'é' => 'e',
        'ê' => 'e',
        'ë' => 'e',
        'ĕ' => 'e',
        'ė' => 'e',
        'ƒ' => 'f',
        'Ĝ' => 'G',
        'Ġ' => 'G',
        'ĝ' => 'g',
        'ġ' => 'g',
        'Ĥ' => 'H',
        'Ħ' => 'H',
        'ĥ' => 'h',
        'ħ' => 'h',
        'Ì' => 'I',
        'Í' => 'I',
        'Î' => 'I',
        'Ï' => 'I',
        'Ĩ' => 'I',
        'Ĭ' => 'I',
        'Ǐ' => 'I',
        'Į' => 'I',
        'Ĳ' => 'IJ',
        'ì' => 'i',
        'í' => 'i',
        'î' => 'i',
        'ï' => 'i',
        'ĩ' => 'i',
        'ĭ' => 'i',
        'ǐ' => 'i',
        'į' => 'i',
        'ĳ' => 'ij',
        'Ĵ' => 'J',
        'ĵ' => 'j',
        'Ĺ' => 'L',
        'Ľ' => 'L',
        'Ŀ' => 'L',
        'ĺ' => 'l',
        'ľ' => 'l',
        'ŀ' => 'l',
        'Ñ' => 'N',
        'ñ' => 'n',
        'ŉ' => 'n',
        'Ò' => 'O',
        'Ô' => 'O',
        'Õ' => 'O',
        'Ō' => 'O',
        'Ŏ' => 'O',
        'Ǒ' => 'O',
        'Ő' => 'O',
        'Ơ' => 'O',
        'Ø' => 'O',
        'Ǿ' => 'O',
        'Œ' => 'OE',
        'ò' => 'o',
        'ô' => 'o',
        'õ' => 'o',
        'ō' => 'o',
        'ŏ' => 'o',
        'ǒ' => 'o',
        'ő' => 'o',
        'ơ' => 'o',
        'ø' => 'o',
        'ǿ' => 'o',
        'œ' => 'oe',
        'Ŕ' => 'R',
        'Ŗ' => 'R',
        'ŕ' => 'r',
        'ŗ' => 'r',
        'Ŝ' => 'S',
        'Ș' => 'S',
        'ŝ' => 's',
        'ș' => 's',
        'ſ' => 's',
        'Ţ' => 'T',
        'Ț' => 'T',
        'Ŧ' => 'T',
        'Þ' => 'TH',
        'ţ' => 't',
        'ț' => 't',
        'ŧ' => 't',
        'þ' => 'th',
        'Ù' => 'U',
        'Ú' => 'U',
        'Û' => 'U',
        'Ũ' => 'U',
        'Ŭ' => 'U',
        'Ű' => 'U',
        'Ų' => 'U',
        'Ư' => 'U',
        'Ǔ' => 'U',
        'Ǖ' => 'U',
        'Ǘ' => 'U',
        'Ǚ' => 'U',
        'Ǜ' => 'U',
        'ù' => 'u',
        'ú' => 'u',
        'û' => 'u',
        'ũ' => 'u',
        'ŭ' => 'u',
        'ű' => 'u',
        'ų' => 'u',
        'ư' => 'u',
        'ǔ' => 'u',
        'ǖ' => 'u',
        'ǘ' => 'u',
        'ǚ' => 'u',
        'ǜ' => 'u',
        'Ŵ' => 'W',
        'ŵ' => 'w',
        'Ý' => 'Y',
        'Ÿ' => 'Y',
        'Ŷ' => 'Y',
        'ý' => 'y',
        'ÿ' => 'y',
        'ŷ' => 'y',
        'Ъ' => '',
        'Ь' => '',
        'А' => 'A',
        'Б' => 'B',
        'Ц' => 'C',
        'Ч' => 'Ch',
        'Д' => 'D',
        'Е' => 'E',
        'Ё' => 'E',
        'Э' => 'E',
        'Ф' => 'F',
        'Г' => 'G',
        'Х' => 'H',
        'И' => 'I',
        'Й' => 'J',
        'Я' => 'Ja',
        'Ю' => 'Ju',
        'К' => 'K',
        'Л' => 'L',
        'М' => 'M',
        'Н' => 'N',
        'О' => 'O',
        'П' => 'P',
        'Р' => 'R',
        'С' => 'S',
        'Ш' => 'Sh',
        'Щ' => 'Shch',
        'Т' => 'T',
        'У' => 'U',
        'В' => 'V',
        'Ы' => 'Y',
        'З' => 'Z',
        'Ж' => 'Zh',
        'ъ' => '',
        'ь' => '',
        'а' => 'a',
        'б' => 'b',
        'ц' => 'c',
        'ч' => 'ch',
        'д' => 'd',
        'е' => 'e',
        'ё' => 'e',
        'э' => 'e',
        'ф' => 'f',
        'г' => 'g',
        'х' => 'h',
        'и' => 'i',
        'й' => 'j',
        'я' => 'ja',
        'ю' => 'ju',
        'к' => 'k',
        'л' => 'l',
        'м' => 'm',
        'н' => 'n',
        'о' => 'o',
        'п' => 'p',
        'р' => 'r',
        'с' => 's',
        'ш' => 'sh',
        'щ' => 'shch',
        'т' => 't',
        'у' => 'u',
        'в' => 'v',
        'ы' => 'y',
        'з' => 'z',
        'ж' => 'zh',
        'Ä' => 'Ae',
        'Ç' => 'C',
        'Ó' => 'O',
        'Ö' => 'Oe',
        'Ü' => 'Ue',
        'ß' => 's',
        'ä' => 'ae',
        'ç' => 'c',
        'ó' => 'o',
        'ö' => 'oe',
        'ü' => 'ue',
        'Ā' => 'A',
        'ā' => 'a',
        'Ą' => 'A',
        'ą' => 'a',
        'Ć' => 'C',
        'ć' => 'c',
        'Č' => 'C',
        'č' => 'c',
        'Ď' => 'D',
        'ď' => 'd',
        'Ē' => 'E',
        'ē' => 'e',
        'Ę' => 'E',
        'ę' => 'e',
        'Ě' => 'E',
        'ě' => 'e',
        'Ğ' => 'G',
        'ğ' => 'g',
        'Ģ' => 'G',
        'ģ' => 'g',
        'Ī' => 'I',
        'ī' => 'i',
        'İ' => 'I',
        'ı' => 'i',
        'Ķ' => 'K',
        'ķ' => 'k',
        'Ļ' => 'L',
        'ļ' => 'l',
        'Ł' => 'l',
        'ł' => 'l',
        'Ń' => 'N',
        'ń' => 'n',
        'Ņ' => 'N',
        'ņ' => 'n',
        'Ň' => 'N',
        'ň' => 'n',
        'Ř' => 'R',
        'ř' => 'r',
        'Ś' => 'S',
        'ś' => 's',
        'Ş' => 'S',
        'ş' => 's',
        'Š' => 'S',
        'š' => 's',
        'Ť' => 'T',
        'ť' => 't',
        'Ū' => 'U',
        'ū' => 'u',
        'Ů' => 'U',
        'ů' => 'u',
        'Ź' => 'Z',
        'ź' => 'z',
        'Ż' => 'Z',
        'ż' => 'z',
        'Ž' => 'Z',
        'ž' => 'z'
    );

	/**
	 * Slugifies a value
	 *
	 * @param string $value The value to be processed
	 * @param array $replace The replace array (keys will be replaced with values)
	 * @param string $group
	 * @return string $value
	 */
	static public function slugify($value, $phoenticReplacements = array(), $group = NULL) {
		$phoenticReplacements = array_merge(self::$phoenticReplacements, $phoenticReplacements);

		if (empty($replace) === FALSE) {
			$value = strtr($value, $replace);
		}

		// Replace non letter or digits by -
		$value = preg_replace('~[^\\pL\d]+~u', '-', $value);
		$value = str_replace('.', '', $value);

		// Trim incl. dashes
		$value = trim($value, '-');

		// Transliterate
		if (function_exists('iconv') === TRUE) {
			$value = iconv('utf-8', 'us-ascii//TRANSLIT', $value);
		}
		// Lowercase
		$value = strtolower($value);

		// Remove unwanted characters
		$value = preg_replace('~[^-\w]+~', '', $value);

		if ($group !== NULL) {
			// ensure we have a unique id per group during this page-load
			if (!isset($GLOBALS[__CLASS__])) {
				$GLOBALS[__CLASS__] = array();
			}
			if (!isset($GLOBALS[__CLASS__][$group])) {
				$GLOBALS[__CLASS__][$group] = array();
			}
			$counter = 1;
			while (in_array($value, $GLOBALS[__CLASS__][$group])) {
				if ($counter > 1) {
					$value = substr($value, 0, -2);
				}
				$value .= '-' . $counter;
				$counter++;
			}
			$GLOBALS[__CLASS__][$group][] = $value;
		}

		return $value;
	}
}
