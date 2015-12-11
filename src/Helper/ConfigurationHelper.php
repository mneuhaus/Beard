<?php

namespace Famelo\Beard\Helper;

use Herrera\Json\Json;
use Famelo\Beard\Configuration;
use RuntimeException;
use Symfony\Component\Console\Helper\Helper;

/**
 * The Box schema file path.
 *
 * @var string
 */
define('BOX_SCHEMA_FILE', BOX_PATH . '/res/schema.json');

/**
 * Manages the acquisition of configuration settings.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class ConfigurationHelper extends Helper {
	/**
	 * The name of the default configuration file.
	 *
	 * @var string
	 */
	const FILE_NAME = 'beard.json';

	/**
	 * The JSON processor.
	 *
	 * @var Json
	 */
	private $json;

	/**
	 * Creates the JSON processor.
	 */
	public function __construct() {
		$this->json = new Json();
	}

	/**
	 * @override
	 */
	public function getName() {
		return 'config';
	}

	/**
	 * Returns the file path to the default configuration file.
	 *
	 * @return string The file path.
	 *
	 * @throws RuntimeException If the default file does not exist.
	 */
	public function getDefaultPath() {
		if (FALSE === file_exists(self::FILE_NAME)) {
			if (FALSE === file_exists(self::FILE_NAME . '.dist')) {
				throw new RuntimeException(
					sprintf('The configuration file could not be found.')
				);
			}

			return realpath(self::FILE_NAME . '.dist');
		}

		return realpath(self::FILE_NAME);
	}

	/**
	 * Loads the configuration file and returns it.
	 *
	 * @param string $file The configuration file path.
	 *
	 * @return Configuration The configuration settings.
	 */
	public function loadFile($file = NULL) {
		if (NULL === $file) {
			$file = $this->getDefaultPath();
		}

		$json = $this->json->decodeFile($file);

		// $this->json->validate(
		// 	$this->json->decodeFile(BOX_SCHEMA_FILE),
		// 	$json
		// );

		return new Configuration($file, $json);
	}
}

?>