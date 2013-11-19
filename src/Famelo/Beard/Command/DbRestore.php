<?php

namespace Famelo\Beard\Command;

use Doctrine\DBAL\DriverManager;
use Famelo\Beard\Configuration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\ProcessBuilder;
use Traversable;

require_once(__DIR__ . '/../../../../vendor/jdorn/sql-formatter/lib/SqlFormatter.php');

/**
 * Builds a new Phar.
 *
 */
class DbRestore extends DbDump {
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
	 * @var DriverManager
	 */
	protected $db;

	/**
	 * @var object
	 */
	protected $schemaManager;

	/**
	 * @override
	 */
	protected function configure() {
		parent::configure();
		$this->setName('db:restore');
		$this->setDescription('Pull the database rows into local files');

		$this->addOption('addHook', null, InputOption::VALUE_NONE,
			'Add a post-merge hook to automatically restore the database from git'
        );
	}

	/**
	 * @override
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->output = $output;
		$this->input = $input;
		$this->baseDir = getcwd() . '/Database/';

		if ($input->getOption('addHook')) {
			$this->addHook();
			return;
		}

		$this->config = $this->getConfig();
		if (isset($this->config['baseDir'])) {
			$this->baseDir = getcwd() . '/' . trim($this->config['baseDir'], '/') . '/';
		}

		$this->restoreDatabase();
	}

	public function restoreDatabase() {
		$command = array('mysql');
		if (isset($this->config['user'])) {
			$command[] = '-u' . $this->config['user'];
		}
		if (isset($this->config['password'])) {
			$command[] = '-p' . $this->config['password'];
		}
		if (isset($this->config['host'])) {
			$command[] = '-h' . $this->config['host'];
		}

		$command[] = $this->config['dbname'];

		$filename = isset($this->config['filename']) ? $this->config['filename'] : $this->config['dbname'] . '.sql';

		$this->output->writeln('Restoring database from ' . $filename);

		$filename = $this->baseDir . $filename;

		$command[] = '< ' . $filename;

		$this->executeShellCommand(implode(' ', $command));
	}

	public function addHook() {
		$filename = getcwd() . '/.git/hooks/post-merge';

		if (!file_exists($filename)) {
			$hook = '#!/bin/sh';
		} else {
			$hook = file_get_contents($filename);
		}

		$php = $this->executeShellCommand('which php');

		$beardCommand = $php . ' ' . $GLOBALS['argv'][0] . ' db:restore';
		if (!stristr($hook, $beardCommand)) {
			$hook.= chr(10) . 'cd ' . getcwd() . ' && ' . $beardCommand;
		}

		file_put_contents($filename, $hook);

		$this->executeShellCommand('chmod +x ' . $filename);

		$filename = getcwd() . '/.git/hooks/post-rebase';

		if (!file_exists($filename)) {
			$hook = '#!/bin/sh';
		} else {
			$hook = file_get_contents($filename);
		}

		$php = $this->executeShellCommand('which php');

		$beardCommand = $php . ' ' . $GLOBALS['argv'][0] . ' db:restore';
		if (!stristr($hook, $beardCommand)) {
			$hook.= chr(10) . 'cd ' . getcwd() . ' && ' . $beardCommand;
		}

		file_put_contents($filename, $hook);

		$this->executeShellCommand('chmod +x ' . $filename);
	}
}

?>