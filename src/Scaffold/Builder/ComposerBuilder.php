<?php

namespace Famelo\Beard\Scaffold\Builder;

/**
 */
class ComposerBuilder {

	/**
	 * @var string
	 */
	protected $filepath;

	/**
	 * @var array
	 */
	protected $data;

	/**
	 * create a new instance representing a composer.json file
	 *
	 * @param string $filepath
	 */
	public function __construct($filepath = 'composer.json') {
		$this->filepath = $filepath;
		if (file_exists($filepath)) {
			$this->data = json_decode(file_get_contents($filepath), TRUE);
		}
	}

	/**
	 * save the composer.json file
	 *
	 * @return void
	 */
	public function save() {
		file_put_contents($this->filepath, json_encode($this->data, JSON_PRETTY_PRINT));
	}

	/**
	 * set a namespace into the composer.json File
	 *
	 * @param string $namespace
	 * @param string $path
	 */
	public function setNamespace($namespace, $path) {
		$this->data['autoload']['psr-4'] = array(
			$namespace => trim($path, '\\') . '\\'
		);
	}

	/**
	 * get the namespace from the current composer.json
	 *
	 * @return string [description]
	 */
	public function getNamespace() {
		return isset($this->data['autoload']['psr-4']) ? key($this->data['autoload']['psr-4']) : NULL;
	}
}

?>
