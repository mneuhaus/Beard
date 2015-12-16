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
class FlowSystem implements SystemSettingsInterface, ClearInterface, WarmupInterface {

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
		'Data/Persistent'
	);

	/**
	 * @var array
	 */
	protected $temporaryTables = array();

	public function __construct($context = 'Development') {
		if (!file_exists('Configuration/Settings.yaml')) {
			return;
		}

		$configuration = (array) Yaml::parse(file_get_contents('Configuration/Settings.yaml'));
		if (file_exists('Configuration/' . $context . '/Settings.yaml')) {
			$configuration = array_replace_recursive($configuration, (array) Yaml::parse(file_get_contents('Configuration/' . $context . '/Settings.yaml')));
		}

		$this->host = isset($configuration['TYPO3']['Flow']['persistence']['backendOptions']['host']) ? $configuration['TYPO3']['Flow']['persistence']['backendOptions']['host'] : NULL;
		$this->database = isset($configuration['TYPO3']['Flow']['persistence']['backendOptions']['dbname']) ? $configuration['TYPO3']['Flow']['persistence']['backendOptions']['dbname'] : NULL;
		$this->username = isset($configuration['TYPO3']['Flow']['persistence']['backendOptions']['user']) ? $configuration['TYPO3']['Flow']['persistence']['backendOptions']['user'] : NULL;
		$this->password = isset($configuration['TYPO3']['Flow']['persistence']['backendOptions']['password']) ? $configuration['TYPO3']['Flow']['persistence']['backendOptions']['password'] : NULL;
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

	/**
	 * @param OutputInterface $output
	 * @return void
	 */
	public function clear($output) {
		$output->writeln('clearing Flow caches');
		system('./flow flow:cache:flush --force');
	}

	/**
	 * @return boolean
	 */
	public function canClear() {
		return file_exists('flow') && file_exists('Packages/Framework/TYPO3.Flow');
	}

	/**
	 * @param OutputInterface $output
	 * @return void
	 */
	public function warmup($output) {
		$output->writeln('warming up Flow caches');
		system('./flow flow:cache:warmup');
	}

	/**
	 * @return boolean
	 */
	public function canWarmup() {
		return $this->canClear();
	}
}

?>
