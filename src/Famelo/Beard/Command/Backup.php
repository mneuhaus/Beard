<?php

namespace Famelo\Beard\Command;

use Famelo\Beard\Backup\Manager;
use Famelo\Beard\Configuration;
use Herrera\Json\Json;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Backup command.
 *
 */
class Backup extends Command {

	/**
	 * The configuration settings.
	 *
	 * @var Configuration
	 */
	protected $config;

	/**
	 * The output handler.
	 *
	 * @var OutputInterface
	 */
	protected $output;

	/**
	 * @override
	 */
	protected function configure() {
		parent::configure();
		$this->setName('backup');
		$this->setDescription('Backup the current directory');
	}

	/**
	 * @override
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->output = $output;
		$this->input = $input;

		$manager = new Manager($this->getConfig(), $output);
		$manager->run();
	}

	/**
	 * @return array
	 */
	public function getConfig() {
		$json = new Json();
		$configuration = $this->convertToArray($json->decodeFile('backup.json'));
		return $configuration;
	}

	/**
	 * @param mixed $data
	 * @return array
	 */
	public function convertToArray($data) {
		if (is_object($data)) {
			$data = get_object_vars($data);
		}
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				$data[$key] = $this->convertToArray($value);
			}
		}
		return $data;
	}
}

?>