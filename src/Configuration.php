<?php

namespace Famelo\Beard;

use Herrera\Box\Compactor\CompactorInterface;

/**
 * Manages the configuration settings.
 *
 */
class Configuration
{
	/**
	 * The configuration file path.
	 *
	 * @var string
	 */
	private $file;

	/**
	 * The raw configuration settings.
	 *
	 * @var object
	 */
	private $raw;

	/**
	 * Sets the raw configuration settings.
	 *
	 * @param string $file The configuration file path.
	 * @param object $raw  The raw settings.
	 */
	public function __construct($file, $raw) {
		$this->file = $file;
		$this->raw = $raw;
	}

	/**
	 * @return array
	 */
	public function getDefaults() {
		if (isset($this->raw->defaults)) {
			return $this->raw->defaults;
		}

		return array();
	}

	/**
	 * @return array
	 */
	public function getChanges() {
		if (isset($this->raw->changes)) {
			$changes = $this->raw->changes;
			foreach ($changes as $change) {
				foreach ($this->getDefaults() as $key => $value) {
					if (!isset($change->$key)) {
						$change->$key = $value;
					}
				}
			}
			return $changes;
		}

		return array();
	}

	/**
	 * @return array
	 */
	public function getDatabase() {
		if (isset($this->raw->database)) {
			return get_object_vars($this->raw->database);
		}

		return array();
	}

}

?>