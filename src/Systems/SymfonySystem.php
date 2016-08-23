<?php
namespace Famelo\Beard\Systems;

use KevinGH\Amend;
use Famelo\Beard\Command;
use Famelo\Beard\Helper;
use Famelo\Beard\Interfaces\Cache\ClearInterface;
use Famelo\Beard\Interfaces\Cache\WarmupInterface;
use Famelo\Beard\Interfaces\SystemSettingsInterface;
use Symfony\Component\Console\Application as Base;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * Clear TYPO3 Caches
 */
class SymfonySystem implements SystemSettingsInterface, ClearInterface, WarmupInterface {

	/**
	 * @var string
	 */
	protected $host;

	/**
	 * @var string
	 */
	protected $database;

	/**
	 * @var string
	 */
	protected $username;

	/**
	 * @var string
	 */
	protected $password;

	/**
	 * @var string
	 */
	protected $databaseType = 'mysql';

	/**
	 * @var array
	 */
	protected $userdataPaths = array(
		'uploads'
	);

	/**
	 * @var array
	 */
	protected $temporaryTables = array();

	public function __construct($context = 'Development') {
		if (!file_exists('app/config/parameters.yml')) {
			return;
		}

		$configuration = (array) Yaml::parse(file_get_contents('app/config/parameters.yml'));

		$this->host = isset($configuration['parameters']['database_host']) ? $configuration['parameters']['database_host'] : NULL;
		$this->database = isset($configuration['parameters']['database_name']) ? $configuration['parameters']['database_name'] : NULL;
		$this->username = isset($configuration['parameters']['database_user']) ? $configuration['parameters']['database_user'] : NULL;
		$this->password = isset($configuration['parameters']['database_password']) ? $configuration['TYPO3']['database_password'] : NULL;
	}

	public function getHost() {
		return $this->host;
	}

	public function getDatabase() {
		return $this->database;
	}

	public function getUsername() {
		return $this->username;
	}

	public function getPassword() {
		return $this->password;
	}

	public function getDatabaseType() {
		return $this->databaseType;
	}

	public function getUserdataPaths() {
		return $this->userdataPaths;
	}

	public function getTemporaryTables() {
		return $this->temporaryTables;
	}

	public function isTemporaryTable($tableName) {
		return FALSE;
	}

	/**
	 * @param OutputInterface $output
	 * @return void
	 */
	public function clear($output) {
		$output->writeln('clearing Flow caches');
		system('./bin/console cache:clear');
	}

	/**
	 * @return boolean
	 */
	public function canClear() {
		return file_exists('bin/console');
	}

	/**
	 * @param OutputInterface $output
	 * @return void
	 */
	public function warmup($output) {
		$output->writeln('warming up Flow caches');
		system('./bin/console cache:warmup');
	}

	/**
	 * @return boolean
	 */
	public function canWarmup() {
		return $this->canClear();
	}
}

?>
