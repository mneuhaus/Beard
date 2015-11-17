<?php

namespace Famelo\Beard;

use KevinGH\Amend;
use Famelo\Beard\Command;
use Famelo\Beard\Helper;
use Symfony\Component\Console\Application as Base;

/**
 * Sets up the application.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class Application extends Base {

	/**
	 * @override
	 */
	public function __construct($name = 'Beard', $version = '@git_tag@') {
		parent::__construct($name, $version);
		define('BEARD_ROOT_DIR', __DIR__);
	}

	/**
	 * @override
	 */
	protected function getDefaultCommands() {
		$commands = parent::getDefaultCommands();
		$commands[] = new Command\Patch();
		$commands[] = new Command\Status();
		$commands[] = new Command\Reset();
		$commands[] = new Command\Setup();
		$commands[] = new Command\Lock();

		if (('@' . 'git_tag@') !== $this->getVersion()) {
			$command = new Amend\Command('update');
			$command->setManifestUri('@manifest_url@');

			$commands[] = $command;
		}

		return $commands;
	}

	/**
	 * @override
	 */
	protected function getDefaultHelperSet() {
		$helperSet = parent::getDefaultHelperSet();
		$helperSet->set(new Helper\ConfigurationHelper());

		if (('@' . 'git_tag@') !== $this->getVersion()) {
			$helperSet->set(new Amend\Helper());
		}

		return $helperSet;
	}
}

?>
