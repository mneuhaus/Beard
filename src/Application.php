<?php

namespace Famelo\Beard;

use KevinGH\Amend;
use Famelo\Beard\Command;
use Famelo\Beard\Helper;
use Symfony\Component\Console\Application as Base;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionCommand;
use Mia3\Koseki\ClassRegister;

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
		ClassRegister::setCacheFile(__DIR__ . '/ClassRegisterCache.php');
	}

	/**
	 * @override
	 */
	protected function getDefaultCommands() {
		$commands = parent::getDefaultCommands();

		$commands[] = new Command\Cache\Clear();
		$commands[] = new Command\Cache\Warmup();

		$commands[] = new Command\Backup();
		$commands[] = new Command\Backup\Userdata();
		$commands[] = new Command\Backup\Database();

		$commands[] = new Command\Database\Clear();
		$commands[] = new Command\Database\Restore();
		$commands[] = new Command\Database\Snapshot();
		$commands[] = new Command\Database\Truncate();

		$commands[] = new Command\Patch();
		$commands[] = new Command\Status();
		$commands[] = new Command\Reset();
		$commands[] = new Command\Setup();
		$commands[] = new Command\Lock();
		$commands[] = new Command\Scaffold();

		$commands[] = new CompletionCommand();

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
