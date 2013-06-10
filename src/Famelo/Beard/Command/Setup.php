<?php

namespace Famelo\Beard\Command;

use Famelo\Beard\Configuration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Traversable;

/**
 * Builds a new Phar.
 *
 */
class Setup extends Command {
	/**
	 * The Box instance.
	 *
	 * @var Box
	 */
	private $box;

	/**
	 * The configuration settings.
	 *
	 * @var Configuration
	 */
	private $config;

	/**
	 * The output handler.
	 *
	 * @var OutputInterface
	 */
	private $output;

	/**
	 * @override
	 */
	protected function configure() {
		parent::configure();
		$this->setName('setup');
		$this->setDescription('Add commit hooks and gerrit push remotes to all repositories');
	}

	/**
	 * @override
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->output = $output;

		$baseDir = getcwd();
		$clean = TRUE;
		exec(sprintf('find %s -name ".git"', $baseDir), $gitWorkingCopies, $status);

		foreach ($gitWorkingCopies as $path) {
			$output = NULL;
			$config = file_get_contents($path . '/config');
			preg_match('/url = git:\/\/git.typo3.org\/FLOW3\/Packages\/(.+).git/', $config, $matches);
			if (count($matches) > 0) {
				$this->addGerritRemote($path);
				$this->addChangeIdCommitHook($path);
			}
		}
	}

	/**
	 * @param string $path
	 * @return void
	 */
	public function addChangeIdCommitHook($path) {
		if (!file_exists($path . '/hooks/commit-msg')) {
			file_put_contents($path . '/hooks/commit-msg', file_get_contents('https://typo3.org/fileadmin/resources/git/commit-msg.txt'));
			$this->output->writeln('Added commit-msg hook to add ChangeId to: ' . realpath($path));
		}
		system('chmod +x ' . $path . '/hooks/commit-msg');
	}

	/**
	 * @param string $path
	 * @return void
	 */
	public function addGerritRemote($path) {
		$configTemplate = '
[remote "gerrit"]
	fetch = +refs/heads/*:refs/remotes/origin/*
	url = git://git.typo3.org/FLOW3/Packages/{package}.git
	push = HEAD:refs/for/master
';
		$config = file_get_contents($path . '/config');
		preg_match('/url = git:\/\/git.typo3.org\/FLOW3\/Packages\/(.+).git/', $config, $matches);
		if (count($matches) > 0 && !stristr($config, '[remote "gerrit"]')) {
			$config .= str_replace('{package}', $matches[1], $configTemplate);
			file_put_contents($path . '/config', $config);
			$this->output->writeln('Added gerrit remote to repository: ' . realpath($path));
		}
	}

}

?>