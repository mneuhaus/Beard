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
class DbDump extends Command {
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
		$this->setName('db:dump');
		$this->setDescription('Pull the database rows into local files');

		$this->addOption('addHook', null, InputOption::VALUE_NONE,
			'Add a pre-commit hook to automatically dump and add the current database'
        );
		$this->addOption('gitAdd', null, InputOption::VALUE_NONE,
			'Add the generated sql file automatically to git to be commited'
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
		$this->initializeDirectory();
		$this->initializeDatabase();

		$filename = $this->dumpDatabase();

		if (isset($this->config['formatSql']) && $this->config['formatSql'] === TRUE) {
			$dump = file_get_contents($filename);
			$formattedDump = \SqlFormatter::format($dump, false);
			$dump = file_put_contents($filename, $formattedDump);
		}

		if ($input->getOption('gitAdd')) {
			$this->executeShellCommand('git add ' . $filename);
		}
	}

	public function getConfig() {
		$config = array(
			'driver' => 'pdo_mysql',
			'baseDir' => '.'
		);

		switch (true) {
			case file_exists('typo3conf/LocalConfiguration.php'):
					$typo3Settings = include_once('typo3conf/LocalConfiguration.php');

					$config = array_merge($config, array(
						'dbname' => $typo3Settings['DB']['database'],
						'user' => $typo3Settings['DB']['username'],
						'password' => $typo3Settings['DB']['password'],
						'host' => $typo3Settings['DB']['host']
					));
				break;

			case file_exists('wp-config.php'):
					include_once('wp-config.php');

					$config = array_merge($config, array(
						'dbname' => DB_NAME,
						'user' => DB_USER,
						'password' => DB_PASSWORD,
						'host' => DB_HOST
					));
				break;
		}

		if (file_exists('beard.json')) {
			$helper = $this->getHelper('config');
			$config = array_merge($config, $helper->loadFile('beard.json')->getDatabase());
		}

		return $config;
	}

	public function initializeDirectory() {
		if (!file_exists($this->baseDir)) {
			mkdir($this->baseDir);
		}

		$htaccessFile = $this->config['baseDir'] . '/.htaccess';
		if (!file_exists($htaccessFile)) {
			file_put_contents($htaccessFile, '');
		}

		$htaccessContent = trim(file_get_contents($htaccessFile));
		if (!stristr($htaccessContent, '<FilesMatch "\.sql$">')) {
			$htaccessContent.= '
<FilesMatch "\.sql$">
deny from all
</FilesMatch>';
			file_put_contents($htaccessFile, $htaccessContent);
		}
	}

	public function initializeDatabase() {
		$config = new \Doctrine\DBAL\Configuration();
		$this->db = DriverManager::getConnection($this->config, $config);
		$this->schemaManager = $this->db->getSchemaManager();
	}

	public function getTableNames() {
		$sql = "SHOW TABLES";
		$results = $this->db->query($sql);
		$tableNames = array();
		foreach ($results as $key => $value) {
			$tableNames[] = current($value);
		}
		return $tableNames;
	}

	public function executeShellCommand($command) {
		$output = '';
		$fp = popen($command, 'r');
		while (($line = fgets($fp)) !== FALSE) {
			$output .= $line;
		}
		pclose($fp);
		return trim($output);
	}

	public function dumpDatabase() {
		$command = array('mysqldump');
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

		$tableNames = $this->getTableNames();
		foreach (explode(',', $this->config['skip']) as $pattern) {
			foreach ($tableNames as $tableName) {
				$result = preg_match('/' . $pattern . '/', $tableName);
				if ($result > 0) {
					$command[] = '--ignore-table=' . $this->config['dbname'] . '.' . $tableName;
				}
			}
		}

		$filename = isset($this->config['filename']) ? $this->config['filename'] : $this->config['dbname'] . '.sql';
		$filename = $this->baseDir . $filename;

		$command[] = '> ' . $filename;

		$this->executeShellCommand(implode(' ', $command));

		return $filename;
	}

	public function addHook() {
		$filename = getcwd() . '/.git/hooks/pre-commit';

		if (!file_exists($filename)) {
			$hook = '#!/bin/sh';
		} else {
			$hook = file_get_contents($filename);
		}

		$php = $this->executeShellCommand('which php');

		$beardCommand = $php . ' ' . $GLOBALS['argv'][0] . ' db:dump --gitAdd';
		if (!stristr($hook, $beardCommand)) {
			$hook.= chr(10) . 'cd ' . getcwd() . ' && ' . $beardCommand;
		}

		file_put_contents($filename, $hook);

		$this->executeShellCommand('chmod +x ' . $filename);
	}
}

?>