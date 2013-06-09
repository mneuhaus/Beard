<?php

namespace Famelo\Beard;

use ArrayIterator;
use Herrera\Box\Compactor\CompactorInterface;
use InvalidArgumentException;
use Phar;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

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

	public function getDefaults() {
		if (isset($this->raw->defaults)) {
			return $this->raw->defaults;
		}

		return array();
	}

	public function getChanges() {
		if (isset($this->raw->changes)) {
			$changes = $this->raw->changes;
			foreach ($changes as $key => $change) {
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

}

?>